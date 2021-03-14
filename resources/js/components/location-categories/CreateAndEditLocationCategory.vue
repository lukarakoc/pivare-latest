<template>
    <div class="modal fade" id="create-and-edit-modal" tabindex="-1" role="dialog"
         aria-labelledby="create-and-edit-modal-label" aria-hidden="true" ref="createAndEditModalRef">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 v-show="editmode" class="modal-title" id="create-and-edit-modal-label">Izmijeni korak</h5>
                    <h5 v-show="!editmode" class="modal-title" id="create-and-edit-modal-label">Dodaj korak</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode ? updateCategory() : storeCategory()">
                    <div class="modal-body">
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'name' + lang.code">Naziv kategorije - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'name' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="name"
                                   placeholder="Unesite naziv kategorije"
                                   :class="{ 'border border-danger': checkIfErrorPresentNames(i) }"
                                   v-model="categoryForm.names[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentNames(i)">
                                {{ categoryErrors.name[`names.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'description-' + lang.code">Opis kategorije - {{
                                    lang.code.toUpperCase()
                                }}*</label>
                            <ckeditor :id="'description-' + lang.code"
                                      name="text"
                                      placeholder="Unesite opis kategorije"
                                      :editor="editor"
                                      :config="editorConfig"
                                      :class="{ 'border border-danger': checkIfErrorPresentDescription(i) }"
                                      v-model="categoryForm.descriptions[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentDescription(i)">
                                {{ categoryErrors.description[`descriptions.${i}`][0] }}
                            </small>
                        </div>
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
                </form>
            </div>
        </div>
    </div>

</template>

<script>

import {EventBus, swalSuccess} from "../../main";
import ImageUploader from "vue-image-upload-resize";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

export default {
    name: "CreateAndEditLocationCategory",
    components: {
        ImageUploader
    },
    data() {
        return {
            storeUpdateDisabled: false,
            editmode: true,
            editor: ClassicEditor,
            languages: [],
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
            categoryForm: {
                id: '',
                names: [],
                descriptions: []
            },
            categoryErrors: {
                name: [],
                nameErrorPresent: false,
                description: [],
                descriptionErrorPresent: false,
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
        createCategory() {
            this.editmode = false;
            this.resetCategoryForm();
            this.resetCategoryFormErrors();
            $('#create-and-edit-modal').modal();
        },
        editCategory(beer) {
            this.editmode = true;
            this.resetCategoryForm();
            this.resetCategoryFormErrors();
            this.fillBeerForm(beer);
            $('#create-and-edit-modal').modal("show");
        },
        storeCategory() {
            this.resetCategoryFormErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.categoryForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.categoryForm.names[i]);
                }

                if (typeof this.categoryForm.descriptions[i] === 'undefined' || this.categoryForm.descriptions[i] === '<p></p>') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.categoryForm.descriptions[i]);
                }
            }

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/location-categories/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-location-categories');
                        swalSuccess("Kategorija je uspješno dodata.");
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
        updateCategory() {
            this.resetCategoryFormErrors();
            const form = new FormData;
            form.append('id', this.categoryForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.categoryForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.categoryForm.names[i]);
                }

                if (typeof this.categoryForm.descriptions[i] === 'undefined') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.categoryForm.descriptions[i]);
                }
            }

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/location-categories/${this.categoryForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-location-categories');
                        swalSuccess("Kategorija je uspješno izmijenjena.");
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
        resetCategoryForm() {
            this.categoryForm.id = '';
            this.categoryForm.names = [];
            this.categoryForm.descriptions = [];
        },
        resetCategoryFormErrors() {
            this.categoryErrors.name = [];
            this.categoryErrors.nameErrorPresent = false;
            this.categoryErrors.description = [];
            this.categoryErrors.descriptionErrorPresent = false;
        },
        fillBeerForm(beer) {
            this.categoryForm.id = beer.id
            this.categoryForm.names[0] = beer.name.me;
            this.categoryForm.names[1] = beer.name.en;
            this.categoryForm.descriptions[0] = beer.description.me;
            this.categoryForm.descriptions[1] = beer.description.en;
        },
        checkForValidationErrors(errors) {

            let names;
            if (Object.keys(errors).some(function (key) {
                names = key;
                return key.startsWith("names");
            })) {
                this.categoryErrors.name = errors;
                this.categoryErrors.nameErrorPresent = true;
            }

            let descriptions;
            if (Object.keys(errors).some(function (key) {
                descriptions = key;
                return key.startsWith("descriptions");
            })) {
                this.categoryErrors.description = errors;
                this.categoryErrors.descriptionErrorPresent = true;
            }
        },
        checkIfErrorPresentNames(i) {
            return this.categoryErrors.name !== '' && this.categoryErrors.name[`names.${i}`] != null;
        },
        checkIfErrorPresentDescription(i) {
            return this.categoryErrors.description !== '' && this.categoryErrors.description[`descriptions.${i}`] != null;
        },
        clearData() {
            this.resetCategoryFormErrors();
            EventBus.$emit('load-location-categories');
        }
    },
    mounted() {
        this.getAllLanguages();
        EventBus.$on('open-create-modal', () => this.createCategory());
        EventBus.$on('open-edit-modal', category => this.editCategory(category));
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
