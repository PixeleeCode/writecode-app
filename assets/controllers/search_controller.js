import { Controller } from "stimulus";

/*
 * Any element with a data-controller="preview" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    // eslint-disable-next-line class-methods-use-this
    toggle(event) {
        if (event.target.id !== "q") {
            const search = document.querySelector("#input-search");
            search.classList.toggle("hidden");
        }
    }
}
