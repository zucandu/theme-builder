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
        document.getElementById("render-payment-gateway").innerHTML = ''
    },

    /**
     * Required function
     * Used to load js and process
     */
    loadScript: () => {

        /**
         * Reset jscript before render or load
         * because the js src or html can be duplicated
         */
        jsPayment.reset()

        var button = document.createElement("button");
        button.setAttribute('class', 'bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full cursor-pointer');
        button.innerHTML = `Pay Now`;
        /* button.innerHTML = `Pay Now ${jsPayment.global_money_format(jsPayment.params.total, jsPayment.currencyDecimalDigits(jsPayment.params.currency))} ${jsPayment.params.currency}`; */

        // 2. Append somewhere
        var body = document.getElementById("render-payment-gateway");
        body.appendChild(button);

        // 3. Add event handler
        button.addEventListener ("click", function() {
            jsPayment.checkoutProcess()
        })

    },

    currencyDecimalDigits: (currency) => {
        if(['JPY', 'TWD', 'VND'].indexOf(currency) > -1) {
            return 0
        }
        return 2
    },

    checkoutProcess: async () => {

        const toast = useToast();

        // Turn on overylay
        jsPayment.overlay(true);

        // Processing order
        const orderStore = useOrderStore();
        await orderStore.processing(jsPayment.params)
            .catch(error => toast.error(error.response.data.message));

        // Turn off overylay
        jsPayment.overlay(false);
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
            divSpinner.className = "animate-spin rounded-full h-16 w-16 border-t-4 border-slate-100";
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