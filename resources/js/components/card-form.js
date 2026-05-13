export function cardForm() {
    return {
        loading: false,
        error: null,

        init() {
            const publicKey = this.$el.getAttribute('data-omise-public-key');

            if (!publicKey) {
                this.error = 'ไม่พบ Omise Public Key กรุณาตรวจสอบการตั้งค่า';
                return;
            }

            const configureOmise = () => {
                if (window.OmiseCard) {
                    window.OmiseCard.configure({
                        publicKey: publicKey,
                    });
                    window.OmiseCard.configureButton('#omise-pay-button');
                    window.OmiseCard.attach();
                }
            };

            if (window.OmiseCard) {
                configureOmise();
            } else {
                const checkOmise = setInterval(() => {
                    if (window.OmiseCard) {
                        clearInterval(checkOmise);
                        configureOmise();
                    }
                }, 100);

                setTimeout(() => clearInterval(checkOmise), 10000);
            }
        },

        openOmiseForm() {
            this.error = null;

            if (!window.OmiseCard) {
                this.error = 'Omise.js ไม่พร้อมใช้งาน กรุณารีโหลดหน้านี้ใหม่';
                return;
            }

            this.loading = true;

            window.OmiseCard.open({
                amount: 9900,
                currency: 'THB',
                defaultPaymentMethod: 'credit_card',
                otherPaymentMethods: 'credit_card',
                frameLabel: 'PromptJod Premium',
                submitLabel: 'ชำระเงิน ฿99.00',
                onCreateTokenSuccess: (token) => {
                    this.loading = false;
                    window.OmiseCard.close();
                    this.$dispatch('subscribe', { token: token });
                },
                onFormClosed: () => {
                    this.loading = false;
                },
            });
        },
    };
}
