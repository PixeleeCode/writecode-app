import { Controller } from "stimulus";

/*
 * Any element with a data-controller="preview" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    initialize() {
        const self = this;
        this.element.addEventListener("change", () => {
            self.preview(self);
        });
    }

    preview(input) {
        if (input.element.files && input.element.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.querySelector("#preview");
                preview.setAttribute("src", e.target.result);
                preview.classList.remove("hidden");
            };

            reader.readAsDataURL(input.element.files[0]); // convert to base64 string
        }
    }
}
