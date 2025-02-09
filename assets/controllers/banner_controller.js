// eslint-disable-next-line import/no-extraneous-dependencies
import { Controller } from "stimulus";

/*
 * Any element with a data-controller="preview" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    // eslint-disable-next-line class-methods-use-this
    initialize() {
        const banner = document.querySelector("#banner");
        const acceptedRGPD = localStorage.rgpd;

        if (acceptedRGPD) {
            banner.classList.add("hidden");
            this.setAcceptedAnalytics();
        } else {
            banner.classList.remove("hidden");
        }
    }

    // eslint-disable-next-line class-methods-use-this
    setAcceptedBanner() {
        localStorage.rgpd = "true";
        this.setAcceptedAnalytics();
        document.querySelector("#banner").classList.add("hidden");
    }

    // eslint-disable-next-line class-methods-use-this
    setNotAcceptedBanner() {
        localStorage.rgpd = "false";
        document.querySelector("#banner").classList.add("hidden");
    }

    /* Global site tag (gtag.js) - Google Analytics */
    // eslint-disable-next-line class-methods-use-this
    setAcceptedAnalytics() {
        if (localStorage.rgpd === "true") {
            const script = document.createElement("script");
            script.async = true;
            script.src = "https://www.googletagmanager.com/gtag/js?id=G-C2JRRK8N63";
            document.body.appendChild(script);

            window.dataLayer = window.dataLayer || [];

            // eslint-disable-next-line no-inner-declarations
            function gtag() {
                // eslint-disable-next-line prefer-rest-params,no-undef
                dataLayer.push(arguments);
            }

            gtag("js", new Date());
            gtag("config", "G-C2JRRK8N63");
        }
    }
}
