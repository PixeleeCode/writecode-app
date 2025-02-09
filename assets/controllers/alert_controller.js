import { Controller } from 'stimulus'

/*
 * Any element with a data-controller="modal" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    time = null

    initialize() {
        this.time = setTimeout(this.hide.bind(this), 5000)
    }

    hide() {
        this.element.classList.add('animate-fade-out-down')
        clearTimeout(this.time)

        this.time = setTimeout(this.close.bind(this), 500)
    }

    close() {
        this.element.classList.add('hidden')
        clearTimeout(this.time)
    }
}
