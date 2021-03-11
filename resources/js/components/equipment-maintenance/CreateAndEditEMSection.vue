<template>
    <div class="modal fade" id="create-and-edit-modal" tabindex="-1" role="dialog"
         aria-labelledby="create-and-edit-modal-label" aria-hidden="true" ref="createAndEditModalRef">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 v-show="editmode" class="modal-title" id="create-and-edit-modal-label">Izmijeni sekciju</h5>
                    <h5 v-show="!editmode" class="modal-title" id="create-and-edit-modal-label">Dodaj sekciju</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode ? updateEMSection() : storeEMSection()">
                    <div class="modal-body">
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'name' + lang.code">Naziv sekcije - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'name' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="name"
                                   placeholder="Unesite naziv sekcije"
                                   :class="{ 'border border-danger': checkIfErrorPresentNames(i) }"
                                   v-model="emSectionForm.names[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentNames(i)">
                                {{ emSectionErrors.name[`names.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'description-' + lang.code">Opis sekcije - {{
                                    lang.code.toUpperCase()
                                }} *</label>
                            <ckeditor :id="'description-' + lang.code"
                                      name="text"
                                      placeholder="Unesite opis sekcije"
                                      :editor="editor"
                                      :config="editorConfig"
                                      :class="{ 'border border-danger': checkIfErrorPresentDescription(i) }"
                                      v-model="emSectionForm.descriptions[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentDescription(i)">
                                {{ emSectionErrors.description[`descriptions.${i}`][0] }}
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
    name: "CreateAndEditEMSection",
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
            emSectionForm: {
                id: '',
                names: [],
                descriptions: []
            },
            emSectionErrors: {
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
        createEMSection() {
            this.editmode = false;
            this.resetEMSection();
            this.resetEMSectionErrors();
            $('#create-and-edit-modal').modal();
        },
        editEMSection(beer) {
            this.editmode = true;
            this.resetEMSection();
            this.resetEMSectionErrors();
            this.fillEMSectionForm(beer);
            $('#create-and-edit-modal').modal("show");
        },
        storeEMSection() {
            this.resetEMSectionErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.emSectionForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.emSectionForm.names[i]);
                }

                if (typeof this.emSectionForm.descriptions[i] === 'undefined' || this.emSectionForm.descriptions[i] === '<p></p>') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.emSectionForm.descriptions[i]);
                }
            }

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/equipment-maintenance-sections/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-equipment-maintenance-sections');
                        swalSuccess("Sekcija je uspješno dodata.");
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
        updateEMSection() {
            this.resetEMSectionErrors();
            const form = new FormData;
            form.append('id', this.emSectionForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.emSectionForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.emSectionForm.names[i]);
                }

                if (typeof this.emSectionForm.descriptions[i] === 'undefined') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.emSectionForm.descriptions[i]);
                }
            }

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/equipment-maintenance-sections/${this.emSectionForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-equipment-maintenance-sections');
                        swalSuccess("QA kategorija je uspješno izmijenjena.");
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
        resetEMSection() {
            this.emSectionForm.id = '';
            this.emSectionForm.names = [];
            this.emSectionForm.descriptions = [];
        },
        resetEMSectionErrors() {
            this.emSectionErrors.name = [];
            this.emSectionErrors.nameErrorPresent = false;
            this.emSectionErrors.description = [];
            this.emSectionErrors.descriptionErrorPresent = false;
        },
        fillEMSectionForm(section) {
            this.emSectionForm.id = section.id
            this.emSectionForm.names[0] = section.name.me;
            this.emSectionForm.names[1] = section.name.en;
            this.emSectionForm.descriptions[0] = section.description.me;
            this.emSectionForm.descriptions[1] = section.description.en;
        },
        checkForValidationErrors(errors) {

            let names;
            if (Object.keys(errors).some(function (key) {
                names = key;
                return key.startsWith("names");
            })) {
                this.emSectionErrors.name = errors;
                this.emSectionErrors.nameErrorPresent = true;
            }

            let descriptions;
            if (Object.keys(errors).some(function (key) {
                descriptions = key;
                return key.startsWith("descriptions");
            })) {
                this.emSectionErrors.description = errors;
                this.emSectionErrors.descriptionErrorPresent = true;
            }
        },
        checkIfErrorPresentNames(i) {
            return this.emSectionErrors.name !== '' && this.emSectionErrors.name[`names.${i}`] != null;
        },
        checkIfErrorPresentDescription(i) {
            return this.emSectionErrors.description !== '' && this.emSectionErrors.description[`descriptions.${i}`] != null;
        },
        clearData() {
            this.resetEMSectionErrors();
            EventBus.$emit('load-equipment-maintenance-sections');
        }
    },
    mounted() {
        this.getAllLanguages();
        EventBus.$on('open-create-modal', () => this.createEMSection());
        EventBus.$on('open-edit-modal', section => this.editEMSection(section));
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
