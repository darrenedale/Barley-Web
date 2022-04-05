import {BarcodeDetailsWidget} from "./BarcodeDetailsWidget.js";

/**
 * Custom component encapsulating a barcode generator.
 *
 * The element contains a barcode details widget and an image. When the user clicks the generate button, the image
 * is updated with a barcode using the provided details.
 */
export class HomePage
{
    protected static readonly ContainerSelector = "section.main-content";
    protected static readonly BarcodeDetailsWidgetSelector = "barcode-details-widget.home-page-barcode-details";
    protected static readonly GenerateButtonSelector = "button.generate-barcode";
    protected static readonly BarcodeImageSelector = "img.barcode-image";

    private readonly m_container: HTMLElement;
    private readonly m_detailsWidget: BarcodeDetailsWidget;
    private readonly m_image: HTMLImageElement;
    private readonly m_generateButton: HTMLButtonElement;
    private readonly m_generateHandler: (event: Event) => void;

    private static m_instance: HomePage;

    public constructor()
    {
        if (HomePage.m_instance) {
            throw "HomePage is a singleton: an instance has already been created.";
        }

        HomePage.m_instance = this;
        this.m_container = this.findContainer();
        this.m_detailsWidget = this.findBarcodeDetailsWidget();
        this.m_image = this.findBarcodeImageElement();
        this.m_generateButton = this.findGenerateButton();

        this.m_generateHandler = (event: Event) => this.onGenerateTriggered();
        this.bindInternalEvents();
    }

    public static get instance(): HomePage
    {
        if (!HomePage.m_instance) {
            HomePage.m_instance = new HomePage();
        }

        return HomePage.m_instance;
    }

    public get detailsWidget(): BarcodeDetailsWidget
    {
        return this.m_detailsWidget;
    }

    public get image(): HTMLImageElement
    {
        return this.m_image;
    }

    public get barcodeType(): string
    {
        return this.m_detailsWidget.barcodeType;
    }

    public set barcodeType(type: string)
    {
        this.m_detailsWidget.barcodeType = type;
    }

    public get barcodeTypes(): string[]
    {
        return this.m_detailsWidget.barcodeTypes;
    }

    public set barcodeTypes(types: string[])
    {
        this.m_detailsWidget.setAttribute("barcode-types", types.join(" "));
    }

    protected findContainer(): HTMLElement
    {
        return <HTMLElement> document.querySelector(HomePage.ContainerSelector);
    }

    protected findComponent<T extends Element>(selector: string, required: boolean = true): T
    {
        console.assert(undefined !== this.m_container, "The home page container is not available.");
        const element = <T> this.m_container.querySelector(selector);
        console.assert(undefined !== element, `The home page container does not contain a required element matching the selector ${selector}.`);
        return element;
    }

    protected findBarcodeDetailsWidget(): BarcodeDetailsWidget
    {
        return this.findComponent(HomePage.BarcodeDetailsWidgetSelector);
    }

    protected findBarcodeImageElement(): HTMLImageElement
    {
        return this.findComponent(HomePage.BarcodeImageSelector);
    }

    protected findGenerateButton(): HTMLButtonElement
    {
        return this.findComponent(HomePage.GenerateButtonSelector);
    }

    protected bindInternalEvents()
    {
        this.m_generateButton.addEventListener("click", this.m_generateHandler);
    }

    protected unbindInternalEvents()
    {
        this.m_detailsWidget.removeEventListener("click", this.m_generateHandler);
    }

    protected onGenerateTriggered()
    {
        this.m_image.src = `/barcode-image/${this.detailsWidget.barcodeType}/${this.detailsWidget.data}/png`;
    }
}

(function() {
    window.addEventListener("load", function() {
        new HomePage();
    })
})();
