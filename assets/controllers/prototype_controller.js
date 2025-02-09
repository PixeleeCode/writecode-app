import { Controller } from 'stimulus'
import Sortable from "sortablejs"

/*
 * Any element with a data-controller="preview" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    static values = {
        index: Number
    }

    connect() {
        this.indexValue = this.restoreIndex()
        this.element.querySelector('.btn-blue').dataset.index = this.indexValue.toString()

        new Sortable(training_chapters, {
            animation: 150,
            handle: '.handle',
            ghostClass: 'sortable-ghost',
            onEnd: this.restoreIndex.bind(this)
        });
    }

    addChapter() {
        const prototype = this.element.dataset.prototype.replace(/__name__/g, this.indexValue)

        this.indexValue = this.indexValue + 1
        this.element.querySelector('.btn-blue').dataset.index = this.indexValue

        document.querySelector('#training_chapters').append(this.createElementFromHTML(prototype))
        this.restoreIndex()
    }

    removeChapter(event) {
        this.indexValue = this.indexValue - 1
        this.element.querySelector('.btn-blue').dataset.index = this.indexValue

        let id = event.currentTarget.dataset.id
        this.element.querySelector('#'+id).remove()

        this.restoreIndex()
    }

    createElementFromHTML(htmlString) {
        const div = document.createElement('span');
        div.innerHTML = htmlString.trim();
        return div.firstChild;
    }

    restoreIndex() {
        let chapters = this.element.querySelectorAll('.list-group-item')
        chapters.forEach((chapter, index) => {
            chapter.querySelectorAll('input[type="hidden"]').forEach(input => {
                input.value = index + 1
            })

            chapter.querySelectorAll('.chapter-id').forEach(element => {
                element.innerText = '#'+ (Number(index) + 1)
            })
        })

        return Number(chapters.length) + 1
    }
}
