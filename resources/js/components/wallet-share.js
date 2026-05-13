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
                    this.$store.toast.success('สร้างลิงก์เชิญเรียบร้อยแล้ว');
                    this.loadInvitations();
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

        openShareModal() {
            this.shareOpen = true;
            this.loadMembers();
            this.loadInvitations();
        },

        closeShareModal() {
            this.shareOpen = false;
        }
    };
}