// eslint-disable-next-line import/no-extraneous-dependencies
import { Controller } from "stimulus";

export default class extends Controller {
    /**
     * Charge un lien de paiement Stripe.
     */
    loadStripe() {
        // eslint-disable-next-line no-undef
        const stripe = Stripe(
            "!ChangeMe!"
        );
        const { id } = this.element.dataset;

        fetch(`/stripe/webhook/subscription/${id}`, {
            method: "POST",
        })
            .then((response) => response.json())
            .then((session) => stripe.redirectToCheckout({ sessionId: session }))
            .then((result) => {
                if (result.error) {
                    const alert = document.querySelector(".alert");
                    alert.querySelector("#errors-stripe").innerHTML = result.error;
                    alert.classList.remove("hidden");
                }
            })
            .catch((error) => {
                const alert = document.querySelector(".alert");
                alert.querySelector("#errors-stripe").innerHTML = error.message;
                alert.classList.remove("hidden");
            });
    }

    /**
     * Archive un prix, le rendant non visible sur WriteCode.
     */
    archive() {
        const { id } = this.element.dataset;
        fetch(`/admin/premium/edit/${id}`, {
            method: "POST",
        }).then((r) => r.json());
    }
}
