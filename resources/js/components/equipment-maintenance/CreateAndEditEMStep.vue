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
                <form @submit.prevent="editmode ? updateStep() : storeStep()">
                    <div class="modal-body">
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'name' + lang.code">Naziv koraka - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'name' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="name"
                                   placeholder="Unesite naziv koraka"
                                   :class="{ 'border border-danger': checkIfErrorPresentNames(i) }"
                                   v-model="stepForm.names[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentNames(i)">
                                {{ stepErrors.name[`names.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'description-' + lang.code">Opis koraka - {{
                                    lang.code.toUpperCase()
                                }}*</label>
                            <ckeditor :id="'description-' + lang.code"
                                      name="text"
                                      placeholder="Unesite opis koraka"
                                      :editor="editor"
                                      :config="editorConfig"
                                      :class="{ 'border border-danger': checkIfErrorPresentDescription(i) }"
                                      v-model="stepForm.descriptions[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentDescription(i)">
                                {{ stepErrors.description[`descriptions.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="video_link">Video link *</label>
                            <input type="url"
                                   class="form-control"
                                   id="video_link"
                                   placeholder="Unesi video link"
                                   :class="{ 'border border-danger': stepErrors.videoLinkErrorPresent}"
                                   v-model="stepForm.videoLink">
                            <small class="text-danger" v-if="stepErrors.videoLinkErrorPresent">
                                {{ stepErrors.videoLink }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-cloak
                             :class="{ 'border border-danger': this.stepErrors.photosErrorPresent}">
                            <input type="file" id="images" multiple accept="image/*"
                                   @change="handleSelects" name="images">
                            <div v-for="(image, i) in this.stepForm.images" class="mt-2">
                                <div class="container">
                                    <img :src="image" class="preview image border" alt="image" width="150"
                                         @click="removeImage(i)">
                                    <div class="overlay" @click="removeImage(i)">
                                        <div class="text">Click to remove</div>
                                    </div>
                                </div>
                                <br>
                            </div>
                        </div>
                        <div class="form-group mx-2 mt-2" v-show="this.stepForm.loadPhotos.length > 0">
                            <div v-for="(image, i) in this.stepForm.loadPhotos" class="mt-2">
                                <div class="container">
                                    <img :src="image.photo_path" class="preview image border" alt="image" width="150"
                                         @click="addToDeleted(image.id, i)">
                                    <div class="overlay" @click="addToDeleted(image.id, i)">
                                        <div class="text">Click to remove</div>
                                    </div>
                                </div>
                                <br>
                            </div>
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
    name: "CreateAndEditEMStep",
    components: {
        ImageUploader
    },
    props: ['section'],
    data() {
        return {
            hasImage: true,
            image: null,
            storeUpdateDisabled: false,
            editmode: true,
            loadImage: '',
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
            stepForm: {
                id: '',
                names: [],
                descriptions: [],
                images: [],
                section: this.$props.section,
                videoLink: '',
                deletePhotos: [],
                loadPhotos: []
            },
            stepErrors: {
                name: [],
                nameErrorPresent: false,
                description: [],
                descriptionErrorPresent: false,
                videoLink: '',
                videoLinkErrorPresent: false,
                photos: '',
                photosErrorPresent: false
            }
        }
    },
    methods: {
        setImage(output) {
            this.loadImage = null;
            this.stepForm.coverPhoto = output;
            this.hasImage = true;
            $('.img-preview').attr('style', 'display: block !important');
        },
        getAllLanguages() {
            axios.get('/admin/languages')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.languages = response.data[1];
                    }
                })
        },
        addToDeleted(photoUrl, index) {
            this.stepForm.deletePhotos.push(photoUrl);
            this.stepForm.loadPhotos.splice(index, 1);
        },
        createStep(data) {
            console.log("Sekcija " + this.section);
            this.editmode = false;
            this.resetStepForm();
            this.resetStepFormErrors();
            $('#create-and-edit-modal').modal();
            $('.img-preview').attr('style', 'display: none !important');
        },
        editStep(step) {
            this.editmode = true;
            this.resetStepForm();
            this.resetStepFormErrors();
            this.fillBeerForm(step);
            $('#create-and-edit-modal').modal("show");
            $('.img-preview').attr('style', 'display: none !important');
        },
        storeStep() {
            this.resetStepFormErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.stepForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.stepForm.names[i]);
                }

                if (typeof this.stepForm.descriptions[i] === 'undefined' || this.stepForm.descriptions[i] === '<p></p>') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.stepForm.descriptions[i]);
                }
            }
            form.append('video_link', this.stepForm.videoLink);
            this.stepForm.images.forEach(image => {
                form.append('photos[]', image)
            });
            form.append('section_id', this.section);

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/equipment-maintenance-step/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-emSteps');
                        swalSuccess("Korak je uspješno dodat.");
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
        updateStep() {
            this.resetStepFormErrors();
            const form = new FormData;
            form.append('id', this.stepForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.stepForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.stepForm.names[i]);
                }

                if (typeof this.stepForm.descriptions[i] === 'undefined') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.stepForm.descriptions[i]);
                }
            }
            form.append('video_link', this.stepForm.videoLink);
            this.stepForm.images.forEach(image => {
                form.append('photos[]', image)
            });

            form.append('section_id', this.section);

            this.stepForm.deletePhotos.forEach(image => {
                form.append('delete_photos[]', image);
            })

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/equipment-maintenance-step/${this.stepForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-emSteps');
                        swalSuccess("Korak je uspješno izmijenjen.");
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
        resetStepForm() {
            this.stepForm.id = '';
            this.stepForm.names = [];
            this.stepForm.descriptions = [];
            this.stepForm.images = [];
            $('#images').val('');
            this.stepForm.videoLink = '';
            this.stepForm.deletePhotos = [];
            this.stepForm.loadPhotos = [];
            this.stepForm.section = '';
        },
        resetStepFormErrors() {
            this.stepErrors.name = [];
            this.stepErrors.nameErrorPresent = false;
            this.stepErrors.description = [];
            this.stepErrors.descriptionErrorPresent = false;
            this.stepErrors.videoLink = '';
            this.stepErrors.videoLinkErrorPresent = false;
            this.stepErrors.photos = '';
            this.stepErrors.photosErrorPresent = false;
        },
        fillBeerForm(step) {
            this.stepForm.id = step.id
            this.stepForm.names[0] = step.name.me;
            this.stepForm.names[1] = step.name.en;
            this.stepForm.descriptions[0] = step.description.me;
            this.stepForm.descriptions[1] = step.description.en;
            this.stepForm.videoLink = step.video_link;
            this.stepForm.loadPhotos = step.photos;
            this.stepForm.section = step.section_id;
        },
        checkForValidationErrors(errors) {

            let names;
            if (Object.keys(errors).some(function (key) {
                names = key;
                return key.startsWith("names");
            })) {
                this.stepErrors.name = errors;
                this.stepErrors.nameErrorPresent = true;
            }

            let descriptions;
            if (Object.keys(errors).some(function (key) {
                descriptions = key;
                return key.startsWith("descriptions");
            })) {
                this.stepErrors.description = errors;
                this.stepErrors.descriptionErrorPresent = true;
            }

            if (errors.hasOwnProperty('video_link')) {
                this.stepErrors.videoLink = errors["video_link"][0];
                this.stepErrors.videoLinkErrorPresent = true;
            }

            if (errors.hasOwnProperty('photos')) {
                this.stepErrors.photos = errors['photos'][0];
                this.stepErrors.photosErrorPresent = true;
            }
        },
        checkIfErrorPresentNames(i) {
            return this.stepErrors.name !== '' && this.stepErrors.name[`names.${i}`] != null;
        },
        checkIfErrorPresentDescription(i) {
            return this.stepErrors.description !== '' && this.stepErrors.description[`descriptions.${i}`] != null;
        },
        checkImageFileSize(imagFile, limitInMb) {
            return imagFile.size > (limitInMb * 1024 * 1024)
        },
        loadImageFile(e) {
            let imageInput = e.target;
            if (imageInput.files && imageInput.files[0]) {
                if (this.checkImageFileSize(imageInput.files[0], 2)) {
                    this.stepErrors.image = "Maksimalna veličina slike je 2 MB.";
                    this.stepErrors.imageErrorPresent = true;
                } else {
                    this.stepErrors.image = "";
                    this.stepErrors.imageErrorPresent = false;

                    this.stepForm.coverPhoto = imageInput.files[0];
                    const reader = new FileReader();
                    reader.onload = evt => this.loadImage = evt.target.result;
                    reader.readAsDataURL(imageInput.files[0]);
                }
            }
            imageInput.value = "";
        },
        clearData() {
            this.resetStepFormErrors();
            EventBus.$emit('load-emSteps');
        },
        handleSelects(e) {
            this.stepForm.images = [];
            let fileList = Array.prototype.slice.call(e.target.files);
            fileList.forEach(f => {

                if (!f.type.match("image.*")) {
                    return;
                }

                let reader = new FileReader();
                let that = this;

                reader.onload = function (e) {
                    that.stepForm.images.push(e.target.result);
                }
                reader.readAsDataURL(f);
            });
        },
        removeImage(index) {
            Array.from($('#images')[0].files).splice(index, 1);
            this.stepForm.images.splice(index, 1);
        }
    },
    mounted() {
        this.getAllLanguages();
        this.section = this.$props.section;
        console.log("Sekcija sada je " + this.section);
        console.log("Sekcija iz forme sada je " + this.stepForm.section);
        EventBus.$on('open-create-modal', data => this.createStep(data));
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
