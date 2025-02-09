// eslint-disable-next-line import/no-extraneous-dependencies
import { Controller } from "stimulus";

/*
 * Any element with a data-controller="preview" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    // eslint-disable-next-line class-methods-use-this
    connect() {
        // eslint-disable-next-line global-require
        const hljs = require("highlight.js");
        // hljs.initHighlighting.called = false;
        // hljs.initHighlighting();
        hljs.highlightAll();
    }
}
