export class BarcodeDetailsWidget extends HTMLElement
{
    public static readonly GenerateTriggeredEvent = "generate-triggered";

    private readonly m_dataInput: HTMLInputElement;
    private readonly m_barcodeType: HTMLSelectElement;
    private readonly m_generateButton: HTMLButtonElement;
    private readonly m_generateHandler: (event: MouseEvent) => void;

    public constructor()
    {
        super();
        this.m_dataInput = this.createDataInputWidget();
        this.m_barcodeType = this.createBarcodeTypeChooserWidget();
        this.m_generateButton = this.createGenerateButton();

        this.m_dataInput.value = this.getAttribute("barcode-data") ?? "";
        this.m_barcodeType.value = this.getAttribute("barcode-type") ?? "";

        let shadow = this.attachShadow({mode: "open"});
        shadow.append(this.m_barcodeType, this.m_dataInput, this.m_generateButton);
        this.m_generateHandler = (event: MouseEvent) => this.onGenerateClicked();
    }

    public static get observedAttributes(): string[]
    {
        return ["barcode-types", "barcode-width", "barcode-height", "placeholder", ];
    }

    public connectedCallback()
    {
        this.bindGenerateButtonEvents();
    }

    public disconnectedCallback()
    {
        this.unbindGenerateButtonEvents();
    }

    public adoptedCallback()
    {}

    public attributeChangedCallback(attributeName: string, oldValue: string, newValue: string )
    {
        switch (attributeName) {
            case "barcode-types":
                this.onBarcodeTypesAttributeChanged(oldValue, newValue);
                return;

            case "barcode-width":
                this.onBarcodeWidthAttributeChanged(oldValue, newValue);
                return;

            case "barcode-height":
                this.onBarcodeHeightAttributeChanged(oldValue, newValue);
                return;

            case "placeholder":
                this.onPlaceholderAttributeChanged(oldValue, newValue);
                return;
        }
    }

    protected onBarcodeTypesAttributeChanged(oldValue: string, newValue: string)
    {
        let oldType = this.barcodeType ?? this.getAttribute("barcode-type");
        this.repopulateBarcodeTypes();
        this.barcodeType = oldType;
    }

    protected onBarcodeDataAttributeChanged(oldValue: string, newValue: string)
    {
        this.data = newValue;
    }

    protected onBarcodeWidthAttributeChanged(oldValue: string, newValue: string)
    {}

    protected onBarcodeHeightAttributeChanged(oldValue: string, newValue: string)
    {}

    protected onPlaceholderAttributeChanged(oldValue: string, newValue: string)
    {
        this.m_dataInput.placeholder = newValue;
    }

    protected bindGenerateButtonEvents()
    {
        this.m_generateButton.addEventListener("click", this.m_generateHandler);
    }

    protected unbindGenerateButtonEvents()
    {
        this.m_generateButton.removeEventListener("click", this.m_generateHandler);
    }

    public get placeholder(): string
    {
        return this.getAttribute("placeholder") ?? "";
    }

    public set placeholder(placeholder: string)
    {
        this.setAttribute("placeholder", placeholder);
    }

    public get barcodeType(): string
    {
        return (<HTMLSelectElement> this.m_barcodeType).value;
    }

    public set barcodeType(type: string)
    {
        this.m_barcodeType.value = type;
    }

    public get data(): string
    {
        return (<HTMLInputElement> this.m_dataInput).value;
    }

    public set data(data: string)
    {
        (<HTMLInputElement> this.m_dataInput).value = data;
    }

    protected createDataInputWidget(): HTMLInputElement
    {
        let input = <HTMLInputElement> document.createElement("input");
        input.name = "data";
        input.type = "text";
        return input;
    }

    protected createBarcodeTypeChooserWidget(): HTMLSelectElement
    {
        let select = <HTMLSelectElement> document.createElement("select");
        select.name = "type";
        return select;
    }

    protected createGenerateButton(): HTMLButtonElement
    {
        let button = <HTMLButtonElement> document.createElement("button");
        button.name = "action";
        button.value = "generate";
        button.appendChild(document.createTextNode("Generate Barcode"));
        return button;
    }

    protected addBarcodeType(type: string, label?: string)
    {
        let option: HTMLOptionElement = (<HTMLSelectElement> this.m_barcodeType).appendChild(document.createElement("option"));
        option.value = type;
        option.appendChild(document.createTextNode(label ?? type));
    }

    protected repopulateBarcodeTypes()
    {
        while (this.m_barcodeType.firstChild) {
            this.m_barcodeType.firstChild.remove();
        }

        if (!this.hasAttribute("barcode-types")) {
            return;
        }

        // @ts-ignore getAttribute can't be null, guarded above
        for (let type of this.getAttribute("barcode-types").split(/ +/)) {
            this.addBarcodeType(type);
        }
    }

    protected onGenerateClicked()
    {
        this.dispatchGenerateTriggeredEvent();
    }

    protected dispatchGenerateTriggeredEvent()
    {
        this.dispatchEvent(new Event(BarcodeDetailsWidget.GenerateTriggeredEvent));
    }
}

(function() {
    window.addEventListener("load", () => {
        window.customElements.define("barcode-details-widget", BarcodeDetailsWidget);
    });
})();
