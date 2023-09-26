Statamic.$components.register('wordpress-users-import-form', {
    template: `
        <div>

            <publish-form
                ref="publishForm"
                :title="title"
                :blueprint="blueprint"
                :values="initialValues"
                :meta="meta"
                :action="url"
                method="patch"
                @saved="onSaved"
            ></publish-form>

            <div class="py-2 border-t flex justify-between">
                <a :href="cancelUrl" class="btn-wp-users btn">Cancel</a>
                <div class="inline-flex">
                    <button v-if="previousUrl" type="submit" class="btn-wp-users btn-primary mr-2" @click="previous">{{ previousText }}</button>
                    <button type="submit" class="btn-wp-users btn-primary" @click="next">{{ nextText }}</button>
                </a>
            </div>
        </div>
    `,

    props: {
        title: String,
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

    data() {
        return {
            button: undefined,
        }
    },

    methods: {
        previous() {
            this.button = 'previous'
            this.$refs.publishForm.submit()
        },

        next() {
            this.button = 'next'
            this.$refs.publishForm.submit()
        },

        onSaved() {
            this.$nextTick(() => window.location = this.button === 'previous' ? this.previousUrl : this.nextUrl);
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
