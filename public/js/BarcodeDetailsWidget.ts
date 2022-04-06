/**
 * Interface for the size of a barcode image.
 */
export interface BarcodeSize
{
    width: number;
    height: number;
}

/**
 * Interface for event dispatched when the selected barcode type changes.
 */
export interface BarcodeTypeChangedEvent extends Event
{
    barcodeType: string;
}

/**
 * Interface for event dispatched when the barcode data changes.
 */
export interface BarcodeDataChangedEvent extends Event {
    barcodeData: string;
}

/**
 * Interface for event dispatched when the barcode size changes.
 */
export interface BarcodeSizeChangedEvent extends Event {
    barcodeSize: BarcodeSize;
}

/**
 * A custom HTML element to capture the details for a barcode.
 *
 * TODO populate types drop-down by doing a BE data fetch if barcode-types-endpoint attr is set. endopoint returns JSON
 *  of [{type: string, label: string,}]
 * TODO if data is multi-line, use POST request?
 */
export class BarcodeDetailsWidget extends HTMLElement
{
    // the name of the custom element tag
    public static readonly TagName = "barcode-details-widget";

    /**
     * The type of event dispatched when the user changes the type.
     *
     * The event dispatched implements the BarcodeTypeChangedEvent interface.
     */
    public static readonly BarcodeTypeChangedEvent = "barcode-type-changed";

    /**
     * The type of event dispatched when the user changes the barcode data.
     *
     * The event dispatched implements the BarcodeDataChangedEvent interface.
     */
    public static readonly BarcodeDataChangedEvent = "barcode-data-changed";

    /**
     * The type of event dispatched when the user changes the size of the barcode image.
     *
     * The event dispatched implements the BarcodeSizeChangedEvent interface.
     */
    public static readonly BarcodeSizeChangedEvent = "barcode-size-changed";

    // the default size to use when size attributes are invalid
    public static readonly DefaultBarcodeSize = {
        width: 500,
        height: 250,
    };

    // component parts
    private readonly m_dataInput: HTMLInputElement;
    private readonly m_barcodeType: HTMLSelectElement;

    // internal event handlers
    private readonly m_typeChangedHandler: (event: Event) => void;
    private readonly m_dataChangedHandler: (event: Event) => void;
    private readonly m_sizeChangedHandler: (event: Event) => void;

    /**
     * Initialise a new BarcodeDetailsWidget.
     *
     * This is the required parameterless constructor for custom elements.
     */
    public constructor()
    {
        super();
        this.m_dataInput = this.createDataInputWidget();
        this.m_barcodeType = this.createBarcodeTypeChooserWidget();

        this.m_dataInput.setAttribute("part", "data-widget");
        this.m_dataInput.value = this.getAttribute("barcode-data") ?? "";
        this.m_barcodeType.setAttribute("part", "barcode-type-widget");
        this.m_barcodeType.value = this.getAttribute("barcode-type") ?? "";

        const shadow = this.attachShadow({mode: "open"});
        shadow.append(this.m_barcodeType, this.m_dataInput);
        this.m_typeChangedHandler = (event: Event) => this.onBarcodeTypeChanged();
        this.m_dataChangedHandler = (event: Event) => this.onBarcodeDataChanged();
        this.m_sizeChangedHandler = (event: Event) => this.onBarcodeSizeChanged();
    }

    /**
     * Custom element attributes that are observed for changes.
     */
    public static get observedAttributes(): string[]
    {
        return ["barcode-types", "placeholder",];
    }

    /**
     * Handler for when the custom element is connected to a DOM.
     */
    public connectedCallback()
    {
        this.bindInternalEventHandlers();
        this.barcodeType = this.getAttribute("barcode-type") ?? "";
        // TODO set width and height widget values to default when available
    }

    /**
     * Handler for when the custom element id disconnected from a DOM.
     */
    public disconnectedCallback()
    {
        this.unbindInternalEventHandlers();
    }

    /**
     * Handler for when one of the observed attributes changes value.
     *
     * @param attributeName The attribute that changed.
     * @param oldValue The previous value of the attribute.
     * @param newValue The new value of the attribute.
     */
    public attributeChangedCallback(attributeName: string, oldValue: string, newValue: string)
    {
        switch (attributeName) {
            case "barcode-types":
                this.onBarcodeTypesAttributeChanged(oldValue, newValue);
                return;

            case "placeholder":
                this.onPlaceholderAttributeChanged(oldValue, newValue);
                return;
        }
    }

    /**
     * Handler for when the barcode-types attribute has changed value.
     *
     * @param oldValue The previous value of the attribute.
     * @param newValue The new value of the attribute.
     * @protected
     */
    protected onBarcodeTypesAttributeChanged(oldValue: string, newValue: string)
    {
        let oldType = this.barcodeType ?? this.getAttribute("barcode-type");
        this.repopulateBarcodeTypes();
        this.barcodeType = oldType;
    }

    /**
     * Handler for when the placeholder atttribute has changed value.
     *
     * @param oldValue The previous value of the attribute.
     * @param newValue The new value of the attribute.
     * @protected
     */
    protected onPlaceholderAttributeChanged(oldValue: string, newValue: string)
    {
        this.m_dataInput.placeholder = newValue;
    }

    /**
     * Fetch the placeholder string for the barcode data.
     */
    public get placeholder(): string
    {
        return this.getAttribute("placeholder") ?? "";
    }

    /**
     * Set the placeholder string for the barcode data.
     *
     * @param placeholder
     */
    public set placeholder(placeholder: string)
    {
        this.setAttribute("placeholder", placeholder);
    }

    /**
     * Fetch the selected barcode type.
     */
    public get barcodeType(): string
    {
        return (<HTMLSelectElement>this.m_barcodeType).value;
    }

    /**
     * Set the selected barcode type.
     *
     * @param type The type to select.
     */
    public set barcodeType(type: string)
    {
        this.m_barcodeType.value = type;
    }

    /**
     * Fetch the current barcode data.
     */
    public get data(): string
    {
        return (<HTMLInputElement>this.m_dataInput).value;
    }

    /**
     * Set the current barcode data.
     *
     * @param data The data to set.
     */
    public set data(data: string)
    {
        (<HTMLInputElement>this.m_dataInput).value = data;
    }

    /**
     * Fetch the current barcode size.
     */
    public get barcodeSize(): BarcodeSize
    {
        // TODO read width and height widgets
        return BarcodeDetailsWidget.DefaultBarcodeSize;
    }

    /**
     * Fetch the current barcode width.
     */
    public get barcodeWidth(): number
    {
        return this.barcodeSize.width;
    }

    /**
     * Fetch the current barcode height.
     */
    public get barcodeHeight(): number
    {
        return this.barcodeSize.height;
    }

    /**
     * Set the current barcode size.
     *
     * @param size The new size for the barcode.
     */
    public set barcodeSize(size: BarcodeSize)
    {
        // TODO set the size
    }

    /**
     * Set the current barcode width.
     *
     * @param width The new width for the barcode.
     */
    public set barcodeWidth(width: number)
    {
        if (0 < width) {
            throw "The width must be > 0.";
        }

        const size = this.barcodeSize;
        size.width = width;
        this.barcodeSize = size;
    }

    /**
     * Set the current barcode width.
     *
     * @param height The new height for the barcode.
     */
    public set barcodeHeight(height: number)
    {
        if (0 < height) {
            throw "The height must be > 0.";
        }

        const size = this.barcodeSize;
        size.height = height;
        this.barcodeSize = size;
    }

    /**
     * Fetch the available barcode types.
     */
    public get barcodeTypes(): string[]
    {
        return this.getAttribute("barcode-types")?.split(/ +/) ?? [];
    }

    /**
     * Set the available barcode types.
     *
     * @param types An array of strings containing the types to make available.
     */
    public set barcodeTypes(types: string[])
    {
        this.setAttribute("barcode-types", types.join(" "));
    }

    /**
     * Helper to create the input widget for the barcode data.
     * @protected
     */
    protected createDataInputWidget(): HTMLInputElement
    {
        const input = <HTMLInputElement>document.createElement("input");
        input.name = "data";
        input.type = "text";
        return input;
    }

    /**
     * Helper to create the chooser widget for the barcode type.
     * @protected
     */
    protected createBarcodeTypeChooserWidget(): HTMLSelectElement
    {
        const select = <HTMLSelectElement>document.createElement("select");
        select.name = "type";
        return select;
    }

    /**
     * Helper to add a type to the barcode type chooser.
     * @protected
     */
    protected addBarcodeType(type: string, label?: string)
    {
        const option: HTMLOptionElement = (<HTMLSelectElement>this.m_barcodeType).appendChild(document.createElement("option"));
        option.value = type;
        option.appendChild(document.createTextNode(label ?? type));
    }

    /**
     * Helper to rebuild the contents of the barcode type chooser from the barcode-types attribute value.
     * @protected
     */
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

    /**
     * Helper to bind all internal event bindings.
     * @protected
     */
    protected bindInternalEventHandlers()
    {
        this.m_barcodeType.addEventListener("change", this.m_typeChangedHandler);

        // TODO is this the correct event?
        this.m_dataInput.addEventListener("change", this.m_dataChangedHandler);

        // TODO bind width and height inputs to size changed event handler
    }

    /**
     * Helper to unbind all internal event bindings.
     * @protected
     */
    protected unbindInternalEventHandlers()
    {
        this.m_barcodeType.removeEventListener("change", this.m_typeChangedHandler);

        // TODO is this the correct event?
        this.m_dataInput.removeEventListener("change", this.m_dataChangedHandler);

        // TODO unbind width and height inputs to size changed event handler
    }

    /**
     * Internal handler for when the user has changed the selected barcode type.
     * @protected
     */
    protected onBarcodeTypeChanged()
    {
        this.dispatchBarcodeTypeChangedEvent(this.barcodeType);
    }

    /**
     * Internal handler for when the user has changed the barcode data.
     * @protected
     */
    protected onBarcodeDataChanged()
    {
        this.dispatchBarcodeDataChangedEvent(this.data);
    }

    /**
     * Internal handler for when the user has changed the barcode size.
     * @protected
     */
    protected onBarcodeSizeChanged()
    {
        this.dispatchBarcodeSizeChangedEvent(this.barcodeSize);
    }

    /**
     * Helper to dispatch the BarcodeTypeChangedEvent.
     * @protected
     */
    protected dispatchBarcodeTypeChangedEvent(type: string)
    {
        const event = <BarcodeTypeChangedEvent>new Event(BarcodeDetailsWidget.BarcodeTypeChangedEvent);
        event.barcodeType = type;
        this.dispatchEvent(event);
    }

    /**
     * Helper to dispatch the BarcodeDataChangedEvent.
     * @protected
     */
    protected dispatchBarcodeDataChangedEvent(data: string)
    {
        const event = <BarcodeDataChangedEvent>new Event(BarcodeDetailsWidget.BarcodeDataChangedEvent);
        event.barcodeData = data;
        this.dispatchEvent(event);
    }

    /**
     * Helper to dispatch the BarcodeSizeChangedEvent.
     * @protected
     */
    protected dispatchBarcodeSizeChangedEvent(size: BarcodeSize)
    {
        const event = <BarcodeSizeChangedEvent>new Event(BarcodeDetailsWidget.BarcodeSizeChangedEvent);
        event.barcodeSize = size;
        this.dispatchEvent(event);
    }
}

(function() {
    window.addEventListener("load", () => {
        window.customElements.define(BarcodeDetailsWidget.TagName, BarcodeDetailsWidget);
    });
})();
