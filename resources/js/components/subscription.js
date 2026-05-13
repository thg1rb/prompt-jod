export function subscriptionModal() {
    return {
        isOpen: false,
        selectedPlan: 'monthly',
        isPaying: false,
        error: null,

        init() {
            this.configureOmise();
        },

        configureOmise() {
            const configure = () => {
                if (window.OmiseCard) {
                    const publicKey = document.querySelector('meta[name="omise-public-key"]')?.getAttribute('content');
                    if (publicKey) {
                        window.OmiseCard.configure({ publicKey });
                    }
                }
            };

            if (window.OmiseCard) {
                configure();
            } else {
                const check = setInterval(() => {
                    if (window.OmiseCard) {
                        clearInterval(check);
                        configure();
                    }
                }, 100);
                setTimeout(() => clearInterval(check), 10000);
            }
        },

        selectPlan(plan) {
            this.selectedPlan = plan;
        },

        pay() {
            if (!this.selectedPlan || this.isPaying) return;

            this.error = null;

            if (!window.OmiseCard) {
                this.error = 'Omise.js ไม่พร้อมใช้งาน กรุณารีโหลดหน้านี้ใหม่';
                return;
            }

            this.isPaying = true;

            const amount = this.selectedPlan === 'yearly' ? 99900 : 9900;

            window.OmiseCard.open({
                amount: amount,
                currency: 'THB',
                defaultPaymentMethod: 'credit_card',
                otherPaymentMethods: 'credit_card',
                frameLabel: 'PromptJod Premium',
                onCreateTokenSuccess: (token) => {
                    window.OmiseCard.close();
                    this.submitSubscription(token);
                },
                onFormClosed: () => {
                    this.isPaying = false;
                },
            });
        },

        async submitSubscription(token) {
            try {
                const response = await fetch('/subscription', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        omise_token: token,
                        plan: this.selectedPlan,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.isOpen = false;
                    window.location.reload();
                } else {
                    this.error = data.message || 'ไม่สามารถสมัครสมาชิกได้';
                    this.isPaying = false;
                }
            } catch (e) {
                this.error = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
                this.isPaying = false;
            }
        },
    };
}
