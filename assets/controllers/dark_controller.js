import { Controller } from "stimulus";

/*
 * Any element with a data-controller="preview" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 */
export default class extends Controller {
    initialize() {
        // Run the code in this function when a user clicks
        const themeSwitcher = this.element.querySelector(".theme-switcher");
        if (themeSwitcher) {
            themeSwitcher.addEventListener("click", (e) => {
                this.setTheme(e);
            });
        }

        // Run the getTheme function when we load the page
        window.addEventListener("load", () => {
            this.getTheme();
        });

        // To enable the theme change when the user updates their device settings
        // eslint-disable-next-line no-unused-vars
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
            this.getTheme();
        });
    }

    getTheme() {
        const localTheme = localStorage.theme ?? "light";
        let selectedButton;

        if (localTheme === "dark") {
            document.documentElement.classList.add("dark");
            selectedButton = "dark";
        } else if (localTheme === "light") {
            document.documentElement.classList.remove("dark");
            selectedButton = "light";
        } else if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
            document.documentElement.classList.add("dark");
            selectedButton = "auto";
        } else {
            document.documentElement.classList.remove("dark");
            selectedButton = "auto";
        }

        this.setActive(selectedButton);
    }

    setTheme(e) {
        const elem = e.target;

        if (elem.classList.contains("theme-switcher-dark")) {
            localStorage.theme = "dark";
        } else if (elem.classList.contains("theme-switcher-light")) {
            localStorage.theme = "light";
        } else {
            localStorage.removeItem("theme");
        }

        this.getTheme();
    }

    setActive(selectedButton) {
        const themeSwitcherButtons = document.querySelectorAll(".theme-switcher-button");
        themeSwitcherButtons.forEach((button) => {
            if (button.classList.contains("text-yellow-500")) {
                button.classList.remove("text-yellow-500");
            }
        });

        const activeButton = document.querySelector(`.theme-switcher-${selectedButton}`);
        activeButton.classList.add("text-yellow-500");
    }
}
