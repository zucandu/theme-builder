import { useOrderStore } from '@/stores/order';
import { useToast } from 'vue-toastification';

const jsPayment = {
    
    params: {},

    /**
     * Required function
     * Used to receive the request data
     */
    setParams: (order) => {
        return jsPayment.params = order
    },

    /**
     * Required function
     * Reset HTML content in the checkout payment button
     */
    reset: () => {
        const e = document.getElementById("squareScript")
        if(e) {
            e.parentNode.removeChild(e)
        }
        document.getElementById("render-payment-gateway").innerHTML = ''
    },

    /**
     * Required function
     * Used to load js and process
     */
    loadScript: () => {

        // Reset js
        jsPayment.reset()

        const cardForm = jsPayment.createCardForm()

        // Append card div into checkout confirm div
        const renderPaymentGateway = document.getElementById("render-payment-gateway")
        renderPaymentGateway.appendChild(cardForm)

        const script = document.createElement("script")
        script.src = "https://sandbox.web.squarecdn.com/v1/square.js"
        if(zucConfig.module_payment_square_environment === 'live') {
            script.src = "https://web.squarecdn.com/v1/square.js"
        }
        script.id = 'squareScript'
        script.addEventListener("load", jsPayment.loadedPayment)
        document.head.appendChild(script)

    },

    /**
     * 
     * @param {*} payments 
     * @returns
     */
    async initializeCard(payments) {
        const card = await payments.card();
        if(card) {
            await card.attach('#card-container');
        } else {
            alert(1)
        }
        return card; 
    },

    /**
     * Load the payment gateway from Square
     * @returns 
     */
    async loadedPayment() {

        // Application Id
        let appId = zucConfig.module_payment_square_sanbox_application_id
        if(zucConfig.module_payment_square_environment === 'live') {
            appId = zucConfig.module_payment_square_production_application_id
        }

        const payments = Square.payments(appId, zucConfig.module_payment_square_location_id);
        let card;
        try {
            card = await jsPayment.initializeCard(payments);
        } catch (e) {
            console.error('Initializing Card failed', e);
            return;
        }
       
        // Handle card token
        const form = document.getElementById('sq-payment-form');

        form.addEventListener('submit', async function(event) {
            
            // disable reload
            event.preventDefault()

            const toast = useToast();

            // Show overlay
            jsPayment.overlay(true)
            

            try {
                // disable the submit button as we await tokenization and make a
                // payment request.
                //document.getElementById('sq-card-button').disabled = true;
                
                const token = await jsPayment.tokenize(card);

                /* const verificationToken = await jsPayment.verifyBuyer(payments, token);
                if(!verificationToken) {
                    jsPayment.overlay(false)
					store.commit('SETTING_SET_ALERT', {
                        'color': 'danger', 
                        'message': 'Verification token failed'
                    })
                    return false
                } */

                const params = { ...jsPayment.params, ...{ 
                    nonce: token,
                    idempotency_key: jsPayment.uuidv4(),
                    /* verification_token: verificationToken */
                }}

                /* const params = Object.assign({}, jsPayment.params, { 
                    nonce: token,
                    idempotency_key: jsPayment.uuidv4(),
                    verification_token: verificationToken
                }) */

                // Call checkout process
                const orderStore = useOrderStore();
                await orderStore.processing(params).catch(error => toast.error(error.response.data.message));

            } catch (e) {
                throw new Error(e)
            } finally {
                jsPayment.overlay(false);
            }

        });

    },

    async verifyBuyer(payments, token) {
        const verificationDetails = {
            amount: jsPayment.params.total,
            /* collected from the buyer */
            billingContact: {
                addressLines: [jsPayment.params.billing.address_line_1],
                familyName: jsPayment.params.billing.name,
                givenName: jsPayment.params.billing.name,
                email: jsPayment.params.profile.email,
                country: jsPayment.params.billing.country_code,
                phone: jsPayment.params.billing.phone,
                postalCode: jsPayment.params.billing.postcode,
                city: jsPayment.params.billing.city,
            },
            currencyCode: jsPayment.params.currency,
            intent: 'CHARGE',
        };
        try {
            const verificationResults = await payments.verifyBuyer(token, verificationDetails);
            return verificationResults.token;
        } catch (e) {
            console.log(e)
        }
    },
    
    // This function tokenizes a payment method. 
    // The ‘error’ thrown from this async function denotes a failed tokenization,
    // which is due to buyer error (such as an expired card). It is up to the
    // developer to handle the error and provide the buyer the chance to fix
    // their mistakes.
    async tokenize(paymentMethod) {
        const tokenResult = await paymentMethod.tokenize();
        if (tokenResult.status === 'OK') {
            return tokenResult.token;
        } else {
            let errorMessage = `Tokenization failed-status: ${tokenResult.status}`;
            if (tokenResult.errors) {
                errorMessage += ` and errors: ${JSON.stringify(
                    tokenResult.errors
                )}`;
            }
            console.log(errorMessage)
            return false
            //throw new Error(errorMessage);
        }
    },

    /**
     * Create card form with some required elements from stripe
     */
    createCardForm: () => {
        
        // 
        const sqCardForm = document.createElement('form')
        sqCardForm.id = 'sq-payment-form'
        sqCardForm.setAttribute('class', 'w-full')

        // Card container
        const sqCardContainer = document.createElement("div")
        sqCardContainer.id = 'card-container'
        sqCardContainer.setAttribute('class', 'bg-gray-100 mb-4 min-h-4 p-4');
        sqCardForm.appendChild(sqCardContainer)

        // Create submit button
        const sqBtn = document.createElement("button")
        sqBtn.id = 'sq-card-button'
        sqBtn.setAttribute('type', 'submit')
        sqBtn.setAttribute('class', 'block w-full text-center bg-gray-900 text-white py-2 rounded hover:bg-gray-600 text-sm shadow cursor-pointer');
        sqBtn.innerHTML = `Pay ${jsPayment.params.total} ${jsPayment.params.currency}`
        sqCardForm.appendChild(sqBtn)

        return sqCardForm
    },

    /**
     * Generate a random UUID as an idempotency key for the payment request
     * length of idempotency_key should be less than 45
     */
    uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8)
            return v.toString(16)
        })
    },

    /**
     * Show/Hide overlay when checkout processing
     */
    overlay(status) {

        let divOverlay = document.getElementById('checkout-overlay')
        if(!divOverlay) {
            divOverlay = document.createElement('div')
            divOverlay.id = 'checkout-overlay'
            let divSpinner = document.createElement('div')
            divSpinner.className = "spinner-grow text-light";
            divOverlay.appendChild(divSpinner)
        }

        divOverlay.setAttribute('style', 'position: fixed;background: #000;width: 100%;top: 0;left: 0;height: 100%;opacity: .6;align-items: center;justify-content: center;z-index: 99999;')

        document.getElementById("render-payment-gateway").appendChild(divOverlay)

        if(status === true) {
            divOverlay.style.display = "flex";
        } else {
            divOverlay.style.display = "none";
        }
    }

}

export default {
    jsPayment
}