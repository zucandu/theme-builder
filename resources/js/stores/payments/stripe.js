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
        const e = document.getElementById("stripeScript")
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
        const checkoutConfirm = document.getElementById("render-payment-gateway")
        checkoutConfirm.appendChild(cardForm)

        const script = document.createElement("script")
        script.src = "https://js.stripe.com/v3/"
        script.id = 'stripeScript'
        script.addEventListener("load", jsPayment.loadedPayment)
        document.head.appendChild(script)

    },

    loadedPayment: () => {

        // Create a Stripe client.
        let publishableKey = zucConfig.module_payment_stripe_testing_publishable_key
        if(zucConfig.module_payment_stripe_environment === 'live') {
            publishableKey = zucConfig.module_payment_stripe_live_publishable_key
        }

        const stripe = Stripe(publishableKey);

        // Create an instance of Elements.
        const elements = stripe.elements();

        // Custom styling can be passed to options when creating an Element.
        // (Note that this demo uses a wider set of styles than the guide below.)
        const style = {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        // Create an instance of the card Element.
        const card = elements.create('card', {style: style});

        // Add an instance of the card Element into the `card-element` <div>.
        card.mount('#card-element');

        // Handle card token
        const form = document.getElementById('payment-form');

        form.addEventListener('submit', function(event) {
			
            // Disable reload
            event.preventDefault();

            // Show overlay
            jsPayment.overlay(true)

            stripe.createToken(card).then(async function(result) {
                if (result.error) {
                    
                    // Inform the customer that there was an error.
                    //const errorElement = document.getElementById('card-errors');
                    //errorElement.textContent = result.error.message
                    
                    console.error(result.error.message);

                    // Hide
                    jsPayment.overlay(false)

                } else {
                    
                    try {
                        // Add token into params
                        const params = { ...jsPayment.params, stripetoken: result.token };

                        // Call checkout process
                        const orderStore = useOrderStore();

                        await orderStore.processing(params);

                    } catch (error) {
                        toast.error(error.response.data.message)
                    } finally {
                        jsPayment.overlay(false);
                    }
                    
                }
            });
        });

    },

    /**
     * Create card form with some required elements from stripe
     */
    createCardForm() {
        // Create form
        const cardForm = document.createElement('form');
        cardForm.id = 'payment-form';
        cardForm.setAttribute('class', 'w-full space-y-4'); // Tailwind: full width + spacing

        // Create card element container
        const cardElement = document.createElement('div');
        cardElement.id = 'card-element';
        cardElement.setAttribute('class', 'p-4 border border-gray-300 rounded-md shadow-sm bg-white'); // Tailwind styles
        cardForm.appendChild(cardElement);

        // Create submit button
        const button = document.createElement('button');
        button.id = 'stripe-pay-now-btn';
        button.setAttribute('type', 'submit');
        button.setAttribute(
            'class',
            'w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition-colors duration-200'
        );
        button.innerHTML = `Pay ${jsPayment.params.total} ${jsPayment.params.currency}`;
        cardForm.appendChild(button);

        return cardForm;
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