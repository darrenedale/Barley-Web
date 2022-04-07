type DomDocument = Document;

namespace Barley
{
    /**
     * Enumeration of the types of login/registration forms available to be switched between in the header.
     */
    export enum HeaderFormType
    {
        LoginForm,
        RegistrationForm,
    }

    /**
     * Extended interface for the main document object, containing a handle for the Barley layout functionality.
     */
    export interface Document extends DomDocument
    {
        barleyMainLayout?: Main;
    }

    /**
     * Semantic alias for the extended DOM document, containing the handle for the Barley layout functionality.
     */
    export var barleyDocument: Document = <Document> document;

    /**
     * Encapsulation of the functionality for the main page layout.
     */
    export class Main
    {
        private static readonly HeaderLoginSwitchSelector = "header .barley-login-selector";
        private static readonly HeaderLoginFormSelector = "header .login-form";
        private static readonly HeaderRegistrationFormSelector = "header .registration-form";

        /**
         * The switch in the header to choose between register/login.
         * @protected
         */
        protected readonly headerLoginSwitch?: HTMLElement;

        /**
         * Initialise the Main layout functionality.
         */
        public constructor()
        {
            if (barleyDocument.barleyMainLayout) {
                throw "Barley main layout Main singleton already created.";
            }

            this.headerLoginSwitch = document.querySelector<HTMLElement>(Main.HeaderLoginSwitchSelector) ?? undefined;

            if (!this.headerLoginSwitch) {
                return;
            }

            this.headerLoginSwitch.addEventListener("click", (event: MouseEvent) => this.onSelectorClicked(event));
            this.selectHeaderForm(HeaderFormType.LoginForm);

            Object.defineProperties(document, {
                "barleyMainLayout": {
                    enumerable: true,
                    writable: false,
                    configurable: false,
                    value: this,
                }
            });
        }

        /**
         * Handler for when the login/registration switch is clicked.
         *
         * @param event
         * @protected
         */
        protected onSelectorClicked(event: MouseEvent)
        {
            console.assert(null !== this.headerLoginSwitch, "Event handler for login switch clicks called without a login switch located.");

            const target = (<HTMLElement>event.target).closest("li")?.dataset["formType"];

            switch (target) {
                case "login":
                    this.onLoginFormSelectorClicked(event);
                    break;

                case "registration":
                    this.onRegistrationFormSelectorClicked(event);
                    break;
            }
        }

        /**
         * Handler for when the selector for the login form is clicked.
         *
         * @param event
         * @protected
         */
        protected onLoginFormSelectorClicked(event: MouseEvent)
        {
            this.selectHeaderForm(HeaderFormType.LoginForm);
        }

        /**
         * Handler for when the selector for the registration form is clicked.
         *
         * @param event
         * @protected
         */
        protected onRegistrationFormSelectorClicked(event: MouseEvent)
        {
            this.selectHeaderForm(HeaderFormType.RegistrationForm);
        }

        /**
         * Locate the container for the login form in the page layout header.
         * @protected
         */
        protected get headerLoginForm(): HTMLElement | null
        {
            return document.querySelector<HTMLElement>(Main.HeaderLoginFormSelector);
        }

        /**
         * Locate the container for the registration form in the page layout header.
         * @protected
         */
        protected get headerRegistrationForm(): HTMLElement | null
        {
            return document.querySelector<HTMLElement>(Main.HeaderRegistrationFormSelector);
        }

        /**
         * Select the registration or login form in the layout header.
         * @protected
         */
        public selectHeaderForm(form: HeaderFormType)
        {
            const loginForm = this.headerLoginForm;
            const registrationForm = this.headerRegistrationForm;

            switch (form) {
                case HeaderFormType.LoginForm:
                    if (loginForm) {
                        loginForm.style.display = "";
                    }

                    if (registrationForm) {
                        registrationForm.style.display = "none";
                    }
                    break;

                case HeaderFormType.RegistrationForm:
                    if (loginForm) {
                        loginForm.style.display = "none";
                    }

                    if (registrationForm) {
                        registrationForm.style.display = "";
                    }
                    break;
            }
        }
    }

    (function (): void
    {
        window.addEventListener("load", () => new Main);
    })();
}
