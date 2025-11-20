<div 
    x-data="{
        id: null,
        name: null,
        value: null,
        init() {
            const el = $el.parentElement;
            this.id = el.dataset.id || 'trix-' + Math.random().toString(36).substr(2, 9);
            this.name = el.dataset.name || '';
            this.value = el.dataset.value || '';
        }
    }"
    x-init="init()"
>
    <input
        type="hidden"
        :name="name"
        :id="id + '_input'"
        :value="value"
    />

    <trix-editor
        :id="id"
        :input="id + '_input'"
        {{ $attributes->merge(['class' => 'bg-white trix-content border-gray-300 focus:ring-1 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm']) }}
    ></trix-editor>
</div>
