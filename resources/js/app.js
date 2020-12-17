Statamic.$components.register('wordpress-users-import-form', {
    template: `
        <publish-container
            v-if="blueprint"
            ref="container"
            name="import"
            reference="import"
            :blueprint="blueprint"
            :values="values"
            :meta="meta"
            :errors="errors"
            @updated="values = $event"
        >
            <div slot-scope="{ setFieldValue, setFieldMeta }">
                <configure-sections
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                    :enable-sidebar="false"/>

                <div class="py-2 border-t flex justify-between">
                    <a :href="cancelUrl" class="btn">Cancel</a>
                    <div class="inline-flex">
                        <button v-if="previousUrl" type="submit" class="btn-primary mr-2" @click="() => submit(false)">{{ previousText }}</button>
                        <button type="submit" class="btn-primary" @click="submit">{{ nextText }}</button>
                    </a>
                </div>
            </div>
        </publish-container>
    `,

    props: {
        blueprint: Object,
        cancelUrl: String,
        initialValues: Object,
        meta: Object,
        nextText: String,
        nextUrl: String,
        previousText: String,
        previousUrl: String,
        url: String,
    },

    data: function() {
        return {
            values: this.initialValues,
            error: null,
            errors: {},
        }
    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        submit(next = true) {
            this.saving = true;
            this.clearErrors();

            this.$axios.patch(this.url, this.values).then(response => {
                this.saving = false;
                this.$refs.container.saved();
                this.$nextTick(() => window.location = next ? this.nextUrl : this.previousUrl);
            }).catch(e => this.handleAxiosError(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
            } else {
                console.log(e);
                this.$toast.error(__('Something went wrong'));
            }
        },
        
    },
})

Statamic.booting(function () {
    Statamic.$axios.get(cp_url('wordpress-users/valid')).then(response => {
        if (! response.data) {
            document.querySelector('.wordpress-users-reminder').style.display = 'block';
        }
    })
});
