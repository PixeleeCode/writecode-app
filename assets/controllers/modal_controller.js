import { Controller } from 'stimulus'

/*
 * Any element with a data-controller="modal" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    static time = null

    show(modal) {
        document.querySelector(modal).classList.remove('hidden')
    }

    show_delete() {
        this.show('#modal')
    }

    show_text() {
        let text = this.element.dataset.text
        let modal = document.querySelector('#modal')

        modal.querySelector('.view-text').innerHTML = text
        this.show('#modal')
    }

    show_course() {
        this.show('#modalCourse')
    }

    show_user() {
        let href = this.element.dataset.href
        let modal = document.querySelector('#modal')

        modal.querySelector('form').action = href
        this.show('#modal')
    }

    show_throttler() {
        this.show_user()
    }

    show_all_throttler() {
        let href = this.element.dataset.href
        let modal = document.querySelector('#modalThrottler')

        modal.querySelector('form').action = href
        this.show('#modalThrottler')
    }

    close() {
        this.element.classList.add('hidden')
    }
}
