export function walletShare(walletId, isOwner) {
    return {
        walletId,
        isOwner,
        shareOpen: false,
        activeTab: 'members',
        members: [],
        invitations: [],
        loading: false,
        generating: false,
        copied: false,
        newInvitationUrl: null,
        expiresAt: null,
        timeRemaining: null,
        countdownInterval: null,
        emailInput: '',
        emailError: '',
        sendingEmail: false,
        showConfirmDialog: false,

        async loadMembers() {
            this.loading = true;
            try {
                const response = await fetch(`/wallets/${this.walletId}/members`);
                const data = await response.json();
                this.members = data.members || [];
            } catch (error) {
                console.error('Failed to load members:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadInvitationLink() {
            this.loading = true;
            try {
                const response = await fetch(`/wallets/${this.walletId}/invitation-link`);
                const data = await response.json();

                if (data.invitation) {
                    const expiresAt = new Date(data.invitation.expires_at);
                    const now = new Date();

                    if (expiresAt > now) {
                        this.newInvitationUrl = data.invitation.url;
                        this.expiresAt = data.invitation.expires_at;
                        this.startCountdown();
                    } else {
                        this.newInvitationUrl = null;
                        this.expiresAt = null;
                        this.timeRemaining = null;
                        this.stopCountdown();
                    }
                } else {
                    this.newInvitationUrl = null;
                    this.expiresAt = null;
                    this.timeRemaining = null;
                    this.stopCountdown();
                }
            } catch (error) {
                console.error('Failed to load invitation link:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadInvitations() {
            this.loading = true;
            try {
                const response = await fetch(`/wallets/${this.walletId}/invitations`);
                const data = await response.json();
                this.invitations = data.invitations || [];
            } catch (error) {
                console.error('Failed to load invitations:', error);
            } finally {
                this.loading = false;
            }
        },

        async generateInvitation() {
            if (this.newInvitationUrl && this.timeRemaining) {
                this.showConfirmDialog = true;
                return;
            }

            await this.doGenerateInvitation();
        },

        async doGenerateInvitation() {
            this.showConfirmDialog = false;
            this.generating = true;
            try {
                const response = await fetch(`/wallets/${this.walletId}/invitations`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.newInvitationUrl = data.invitation.url;
                    this.expiresAt = data.invitation.expires_at;
                    this.$store.toast.success('สร้างลิงก์เชิญเรียบร้อยแล้ว');
                    this.loadInvitationLink();
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to generate invitation:', error);
                this.$store.toast.error('เกิดข้อผิดพลาดในการสร้างลิงก์เชิญ');
            } finally {
                this.generating = false;
            }
        },

        startCountdown() {
            this.stopCountdown();
            this.updateCountdown();

            this.countdownInterval = setInterval(() => {
                this.updateCountdown();
            }, 1000);
        },

        stopCountdown() {
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }
        },

        updateCountdown() {
            if (!this.expiresAt) {
                this.timeRemaining = null;
                this.stopCountdown();
                return;
            }

            const expiresAt = new Date(this.expiresAt);
            const now = new Date();
            const diff = Math.floor((expiresAt - now) / 1000);

            if (diff <= 0) {
                this.timeRemaining = null;
                this.newInvitationUrl = null;
                this.expiresAt = null;
                this.stopCountdown();
                return;
            }

            this.timeRemaining = this.formatTimeRemaining(diff);
        },

        formatTimeRemaining(totalSeconds) {
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            const parts = [];

            if (hours > 0) {
                parts.push(`${hours} ชั่วโมง`);
            }
            if (minutes > 0) {
                parts.push(`${minutes} นาที`);
            }
            if (seconds > 0 || parts.length === 0) {
                parts.push(`${seconds} วินาที`);
            }

            return parts.join(' ');
        },

        async removeMember(userId) {
            if (!confirm('คุณต้องการลบสมาชิกนี้ใช่หรือไม่? ธุรกรรมของพวกเขาจะถูกลบด้วย')) {
                return;
            }

            try {
                const response = await fetch(`/wallets/${this.walletId}/members/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.$store.toast.success('ลบสมาชิกเรียบร้อยแล้ว');
                    this.loadMembers();
                    this.loadInvitations();
                } else {
                    this.$store.toast.error(data.message || 'เกิดข้อผิดพลาด');
                }
            } catch (error) {
                console.error('Failed to remove member:', error);
                this.$store.toast.error('เกิดข้อผิดพลาดในการลบสมาชิก');
            }
        },

        async copyInvitationUrl() {
            if (!this.newInvitationUrl) return;

            try {
                await navigator.clipboard.writeText(this.newInvitationUrl);
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 2000);
            } catch (error) {
                console.error('Failed to copy:', error);
            }
        },

        async sendInvitationEmail() {
            this.emailError = '';
            const email = this.emailInput.trim();

            if (!email) {
                this.emailError = 'กรุณาระบุอีเมล';
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                this.emailError = 'รูปแบบอีเมลไม่ถูกต้อง';
                return;
            }

            this.sendingEmail = true;
            try {
                const response = await fetch(`/wallets/${this.walletId}/invitations/send-email`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email })
                });
                const data = await response.json();
                if (data.success) {
                    this.$store.toast.success(data.message);
                    this.emailInput = '';
                    this.loadInvitations();
                } else {
                    this.emailError = data.message || 'เกิดข้อผิดพลาด';
                }
            } catch (error) {
                console.error('Failed to send invitation email:', error);
                this.$store.toast.error('เกิดข้อผิดพลาดในการส่งอีเมล');
            } finally {
                this.sendingEmail = false;
            }
        },

        openShareModal() {
            this.shareOpen = true;
            this.loadMembers();
            this.loadInvitationLink();
            this.loadInvitations();
        },

        closeShareModal() {
            this.shareOpen = false;
            this.stopCountdown();
            this.showConfirmDialog = false;
        },

        cancelGenerateInvitation() {
            this.showConfirmDialog = false;
        }
    };
}