<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendWalletInvitationRequest;
use App\Jobs\SendWalletInvitationEmail;
use App\Models\Wallet;
use App\Models\WalletMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletMemberController extends Controller
{
    private const MAX_MEMBERS = 10;

    public function members(Wallet $wallet): JsonResponse
    {
        abort_if(! $wallet->isOwner(auth()->user()), 403);

        $members = $wallet->acceptedMembers()
            ->with('user:id,name,email')
            ->get()
            ->map(fn ($member) => [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'name' => $member->user->name,
                'email' => $member->user->email,
                'joined_at' => $member->accepted_at->format('Y-m-d H:i:s'),
            ]);

        return response()->json(['members' => $members]);
    }

    public function invitations(Wallet $wallet): JsonResponse
    {
        abort_if(! $wallet->isOwner(auth()->user()), 403);

        $invitations = $wallet->invitations()
            ->with('user:id,name,email')
            ->get()
            ->map(fn ($invitation) => [
                'id' => $invitation->id,
                'user_id' => $invitation->user_id,
                'name' => $invitation->user->name,
                'email' => $invitation->user->email,
                'token' => $invitation->token,
                'expires_at' => $invitation->token_expires_at->format('Y-m-d H:i:s'),
                'created_at' => $invitation->created_at->format('Y-m-d H:i:s'),
            ]);

        return response()->json(['invitations' => $invitations]);
    }

    public function createInvitation(Wallet $wallet): JsonResponse
    {
        abort_if(! $wallet->isOwner(auth()->user()), 403);

        $memberCount = $wallet->member_count;
        if ($memberCount >= self::MAX_MEMBERS) {
            return response()->json([
                'success' => false,
                'message' => 'จำนวนสมาชิกในกระเป๋าเงินถึงขีดจำกัดแล้ว (สูงสุด '.self::MAX_MEMBERS.' คน)',
            ], 400);
        }

        $token = Str::random(64);
        $member = $wallet->members()->create([
            'user_id' => auth()->id(),
            'invited_by' => auth()->id(),
            'token' => $token,
            'token_expires_at' => now()->addHours(24),
        ]);

        $invitationUrl = route('invitations.accept', ['token' => $token]);

        return response()->json([
            'success' => true,
            'invitation' => [
                'id' => $member->id,
                'token' => $token,
                'url' => $invitationUrl,
                'expires_at' => $member->token_expires_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function sendInvitationEmail(SendWalletInvitationRequest $request, Wallet $wallet): JsonResponse
    {
        $email = $request->validated()['email'];

        $token = Str::random(64);
        $member = $wallet->members()->create([
            'user_id' => null,
            'invited_by' => auth()->id(),
            'email' => $email,
            'token' => $token,
            'token_expires_at' => now()->addHours(24),
        ]);

        SendWalletInvitationEmail::dispatch($wallet, $member, $email);

        return response()->json([
            'success' => true,
            'message' => 'ส่งเชิญไปที่ '.$email.' แล้ว',
        ]);
    }

    public function removeMember(Wallet $wallet, string $userId): JsonResponse
    {
        abort_if(! $wallet->isOwner(auth()->user()), 403);

        $member = $wallet->members()->where('user_id', $userId)->first();

        if (! $member) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบสมาชิกนี้ในกระเป๋าเงิน',
            ], 404);
        }

        if ($userId === $wallet->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถลบเจ้าของกระเป๋าเงิน',
            ], 400);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบสมาชิกเรียบร้อยแล้ว',
        ]);
    }

    public function showInvitation(string $token): View|RedirectResponse
    {
        $member = WalletMember::where('token', $token)
            ->validToken()
            ->first();

        if (! $member) {
            return redirect()->route('dashboard')
                ->with('error', 'ลิงก์เชิญไม่ถูกต้องหรือหมดอายุแล้ว');
        }

        $wallet = $member->wallet;
        $owner = $wallet->user;

        if ($member->isAccepted()) {
            return redirect()->route('wallets.show', $wallet)
                ->with('info', 'คุณเป็นสมาชิกของกระเป๋าเงินนี้แล้ว');
        }

        $isLoggedIn = auth()->check();
        $isAlreadyMember = $isLoggedIn && ($wallet->hasMember(auth()->user()) || $wallet->isOwner(auth()->user()));

        return view('invitations.accept', [
            'wallet' => $wallet,
            'owner' => $owner,
            'token' => $token,
            'isLoggedIn' => $isLoggedIn,
            'isAlreadyMember' => $isAlreadyMember,
        ]);
    }

    public function acceptInvitation(string $token): RedirectResponse|JsonResponse
    {
        $member = WalletMember::where('token', $token)
            ->validToken()
            ->first();

        if (! $member) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ลิงก์เชิญไม่ถูกต้องหรือหมดอายุแล้ว',
                ], 404);
            }

            return redirect()->route('dashboard')
                ->with('error', 'ลิงก์เชิญไม่ถูกต้องหรือหมดอายุแล้ว');
        }

        if (! auth()->check()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาเข้าสู่ระบบก่อนเข้าร่วมกระเป๋าเงิน',
                ], 401);
            }

            return redirect()->route('login')
                ->with('info', 'กรุณาเข้าสู่ระบบก่อนเข้าร่วมกระเป๋าเงิน');
        }

        if ($member->wallet->isOwner(auth()->user())) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'คุณเป็นเจ้าของกระเป๋าเงินนี้อยู่แล้ว',
                ], 400);
            }

            return redirect()->route('wallets.show', $member->wallet)
                ->with('info', 'คุณเป็นเจ้าของกระเป๋าเงินนี้อยู่แล้ว');
        }

        if (auth()->user()->isFree()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาสมัครสมาชิก Premium เพื่อเข้าร่วมกระเป๋าเงินแชร์',
                    'requires_subscription' => true,
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'กรุณาสมัครสมาชิก Premium เพื่อเข้าร่วมกระเป๋าเงินแชร์');
        }

        if ($member->isAccepted()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'คุณเป็นสมาชิกของกระเป๋าเงินนี้แล้ว',
                ], 400);
            }

            return redirect()->route('wallets.show', $member->wallet)
                ->with('info', 'คุณเป็นสมาชิกของกระเป๋าเงินนี้แล้ว');
        }

        $member->update([
            'user_id' => auth()->id(),
            'accepted_at' => now(),
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'เข้าร่วมกระเป๋าเงิน '.$member->wallet->name.' เรียบร้อยแล้ว',
            ]);
        }

        return redirect()->route('wallets.show', $member->wallet)
            ->with('success', 'เข้าร่วมกระเป๋าเงิน '.$member->wallet->name.' เรียบร้อยแล้ว');
    }
}
