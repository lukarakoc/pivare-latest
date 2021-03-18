<template>
    <div class="modal fade" id="create-and-edit-modal" tabindex="-1" role="dialog"
         aria-labelledby="create-and-edit-modal-label" aria-hidden="true" ref="createAndEditModalRef">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 v-show="editmode" class="modal-title" id="create-and-edit-modal-label">Izmijeni popust</h5>
                    <h5 v-show="!editmode" class="modal-title" id="create-and-edit-modal-label">Dodaj popust</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode ? updateDiscount() : storeDiscount()">
                    <div class="modal-body">
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'name' + lang.code">Naziv popusta - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'name' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="name"
                                   placeholder="Unesite naziv popusta"
                                   :class="{ 'border border-danger': checkIfErrorPresentNames(i) }"
                                   v-model="discountForm.names[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentNames(i)">
                                {{ discountErrors.name[`names.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'description-' + lang.code">Opis popusta - {{
                                    lang.code.toUpperCase()
                                }}*</label>
                            <ckeditor :id="'description-' + lang.code"
                                      name="text"
                                      placeholder="Unesite opis popusta"
                                      :editor="editor"
                                      :config="editorConfig"
                                      :class="{ 'border border-danger': checkIfErrorPresentDescription(i) }"
                                      v-model="discountForm.descriptions[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentDescription(i)">
                                {{ discountErrors.description[`descriptions.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="amount">Iznos popusta *</label>
                            <input type="text"
                                   class="form-control"
                                   id="amount"
                                   placeholder="Unesite iznos popusta"
                                   :class="{ 'border border-danger': discountErrors.amountErrorPresent}"
                                   v-model="discountForm.amount">
                            <small class="text-danger" v-if="discountErrors.amountErrorPresent">
                                {{ discountErrors.amount }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="dateFrom">Datum od *</label>
                            <div class="input-group"
                                 :style="{'border border-danger' : discountErrors.dateFromErrorPresent}">
                                <flat-pickr v-model="discountForm.dateFrom" :config="configDate"
                                            placeholder="Izaberi datum od" name="birthdate"
                                            class="form-control"
                                            :style="{'border border-danger' : discountErrors.dateToErrorPresent}"></flat-pickr>
                                <div class="input-group-append">
                                    <button class="btn btn-default" type="button" title="Toggle" data-toggle>
                                        <i class="fa fa-calendar">
                                            <span aria-hidden="true" class="sr-only">Toggle</span>
                                        </i>
                                    </button>
                                    <button class="btn btn-default" type="button" title="Clear" data-clear>
                                        <i class="fa fa-times">
                                            <span aria-hidden="true" class="sr-only">Clear</span>
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-danger" v-if="discountErrors.dateFromErrorPresent">
                                {{ discountErrors.dateFrom }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="dateTo">Datum do *</label>
                            <div class="input-group"
                                 :style="{'border border-danger' : discountErrors.dateToErrorPresent}">
                                <flat-pickr v-model="discountForm.dateTo" :config="configDate"
                                            placeholder="Izaberi datum do" name="birthdate"
                                            class="form-control"
                                            :style="{'border border-danger' : discountErrors.dateToErrorPresent}"></flat-pickr>
                                <div class="input-group-append">
                                    <button class="btn btn-default" type="button" title="Toggle" data-toggle>
                                        <i class="fa fa-calendar">
                                            <span aria-hidden="true" class="sr-only">Toggle</span>
                                        </i>
                                    </button>
                                    <button class="btn btn-default" type="button" title="Clear" data-clear>
                                        <i class="fa fa-times">
                                            <span aria-hidden="true" class="sr-only">Clear</span>
                                        </i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-danger" v-if="discountErrors.dateToErrorPresent">
                                {{ discountErrors.dateTo }}
                            </small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Odustani</button>
                            <button id="updateUser"
                                    class="btn btn-primary"
                                    type="submit"
                                    v-show="editmode"
                                    @click="storeUpdateDisabled = true">
                                Sačuvaj
                                <span class="spinner-border-sm"
                                      role="status"
                                      aria-hidden="true"
                                      :class="{ 'spinner-border': storeUpdateDisabled }"></span>
                            </button>
                            <button id="storeUser"
                                    class="btn btn-primary"
                                    type="submit"
                                    v-show="!editmode"
                                    @click="storeUpdateDisabled = true">
                                Dodaj
                                <span class="spinner-border-sm"
                                      role="status"
                                      aria-hidden="true"
                                      :class="{ 'spinner-border': storeUpdateDisabled }"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</template>

<script>

import {EventBus, swalSuccess} from "../../main";
import ImageUploader from "vue-image-upload-resize";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

export default {
    name: "CreateAndEditDiscount",
    components: {
        ImageUploader,
        flatPickr
    },
    props: ['location'],
    data() {
        return {
            hasImage: true,
            image: null,
            storeUpdateDisabled: false,
            editmode: true,
            loadImage: '',
            editor: ClassicEditor,
            languages: [],
            configDate: {
                mode: 'single',
                wrap: true,
                altInput: true,
                dateFormat: 'd.m.Y',
                altFormat: 'd.m.Y'
            },
            editorConfig: {
                toolbar: {
                    items: [
                        'heading',
                        'bold',
                        'italic',
                        'link',
                        'BulletedList',
                        'numberedList',
                        'undo',
                        'redo'
                    ]
                }
            },
            config: {
                wrap: true,
                altInput: true,
                dateFormat: 'd.m.Y.',
                altFormat: 'd.m.Y.'
            },
            discountForm: {
                id: '',
                names: [],
                descriptions: [],
                dateFrom: '',
                dateTo: '',
                amount: '',
                locationId: this.location,
            },
            discountErrors: {
                name: [],
                nameErrorPresent: false,
                description: [],
                descriptionErrorPresent: false,
                amount: '',
                amountErrorPresent: false,
                dateFrom: '',
                dateFromErrorPresent: false,
                dateTo: '',
                dateToErrorPresent: false,
                location: '',
                locationErrorPresent: false
            }
        }
    },
    methods: {
        getAllLanguages() {
            axios.get('/admin/languages')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.languages = response.data[1];
                    }
                })
        },
        createDiscount(data) {
            this.editmode = false;
            this.resetDiscount();
            this.resetDiscountErrors();
            $('#create-and-edit-modal').modal();
            $('.img-preview').attr('style', 'display: none !important');
        },
        editStep(step) {
            this.editmode = true;
            this.resetDiscount();
            this.resetDiscountErrors();
            this.fillDiscountForm(step);
            $('#create-and-edit-modal').modal("show");
            $('.img-preview').attr('style', 'display: none !important');
        },
        storeDiscount() {
            this.resetDiscountErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.discountForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.discountForm.names[i]);
                }

                if (typeof this.discountForm.descriptions[i] === 'undefined' || this.discountForm.descriptions[i] === '<p></p>') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.discountForm.descriptions[i]);
                }
            }
            form.append('date_from', this.discountForm.dateFrom);
            form.append('date_to', this.discountForm.dateTo);
            form.append('amount', this.discountForm.amount);
            form.append('location', this.discountForm.locationId);

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/locations-discounts/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-discounts');
                        swalSuccess("Popust je uspješno dodat.");
                    }
                }).catch(error => {
                this.storeUpdateDisabled = false;
                if (error.response.status === 422) {
                    this.checkForValidationErrors(error.response.data.errors);
                }
                console.log(error.response.data);
                console.log(error.response.status);
                console.log(error.response.headers);
            });
        },
        updateDiscount() {
            this.resetDiscountErrors();
            const form = new FormData;
            form.append('id', this.discountForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.discountForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.discountForm.names[i]);
                }

                if (typeof this.discountForm.descriptions[i] === 'undefined') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.discountForm.descriptions[i]);
                }
            }
            form.append('date_from', this.discountForm.dateFrom);
            form.append('date_to', this.discountForm.dateTo);
            form.append('amount', this.discountForm.amount);
            form.append('location', this.discountForm.locationId);

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/locations-discounts/${this.discountForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-discounts');
                        swalSuccess("Popust je uspješno izmijenjen.");
                    }
                }).catch(error => {
                this.storeUpdateDisabled = false;
                if (error.response.status === 422) {
                    this.checkForValidationErrors(error.response.data.errors);
                }
                console.log(error.response.data);
                console.log(error.response.status);
                console.log(error.response.headers);
            });
        },
        resetDiscount() {
            this.discountForm.id = '';
            this.discountForm.names = [];
            this.discountForm.descriptions = [];
            this.discountForm.dateFrom = '';
            this.discountForm.dateTo = '';
            this.discountForm.amount = '';
        },
        resetDiscountErrors() {
            this.discountErrors.name = [];
            this.discountErrors.nameErrorPresent = false;
            this.discountErrors.description = [];
            this.discountErrors.descriptionErrorPresent = false;
            this.discountErrors.dateFrom = '';
            this.discountErrors.dateFromErrorPresent = false;
            this.discountErrors.dateTo = '';
            this.discountErrors.dateToErrorPresent = false;
            this.discountErrors.amount = '';
            this.discountErrors.amountErrorPresent = false;
        },
        fillDiscountForm(discount) {
            this.discountForm.id = discount.id
            this.discountForm.names[0] = discount.name.me;
            this.discountForm.names[1] = discount.name.en;
            this.discountForm.descriptions[0] = discount.description.me;
            this.discountForm.descriptions[1] = discount.description.en;
            this.discountForm.dateFrom = discount.date_from;
            this.discountForm.dateTo = discount.date_to;
            this.discountForm.locationId = discount.location_id;
            this.discountForm.amount = discount.amount;
        },
        checkForValidationErrors(errors) {

            let names;
            if (Object.keys(errors).some(function (key) {
                names = key;
                return key.startsWith("names");
            })) {
                this.discountErrors.name = errors;
                this.discountErrors.nameErrorPresent = true;
            }

            let descriptions;
            if (Object.keys(errors).some(function (key) {
                descriptions = key;
                return key.startsWith("descriptions");
            })) {
                this.discountErrors.description = errors;
                this.discountErrors.descriptionErrorPresent = true;
            }

            if (errors.hasOwnProperty('date_from')) {
                this.discountErrors.dateFrom = errors["date_from"][0];
                this.discountErrors.dateFromErrorPresent = true;
            }

            if (errors.hasOwnProperty('date_to')) {
                this.discountErrors.dateTo = errors['date_to'][0];
                this.discountErrors.dateToErrorPresent = true;
            }

            if (errors.hasOwnProperty('amount')) {
                this.discountErrors.amount = errors['amount'][0];
                this.discountErrors.amountErrorPresent = true;
            }

            if (errors.hasOwnProperty('location')) {
                this.discountErrors.location = errors['location'][0];
                this.discountErrors.locationErrorPresent = true;
            }
        },
        checkIfErrorPresentNames(i) {
            return this.discountErrors.name !== '' && this.discountErrors.name[`names.${i}`] != null;
        },
        checkIfErrorPresentDescription(i) {
            return this.discountErrors.description !== '' && this.discountErrors.description[`descriptions.${i}`] != null;
        },
        clearData() {
            this.resetDiscountErrors();
            EventBus.$emit('load-discounts');
        }
    },
    mounted() {
        this.getAllLanguages();
        this.discountForm.locationId = this.$props.location;
        EventBus.$on('open-create-modal', data => this.createDiscount(data));
        EventBus.$on('open-edit-modal', step => this.editStep(step));
        $(this.$refs.createAndEditModalRef).on("hidden.bs.modal", this.clearData);
    }
}
</script>

<style scoped>
.container {
    position: relative;
}

.image {
    display: block;
    width: 150px;
}

.overlay {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    height: auto;
    width: 170px;
    opacity: 0;
    transition: .5s ease;
    background-color: #008CBA;
}

.container:hover .overlay {
    opacity: 1;
}

.text {
    color: white;
    font-size: 16px;
    position: absolute;
    top: 50%;
    left: 50%;
    -webkit-transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    text-align: center;
}
</style>
