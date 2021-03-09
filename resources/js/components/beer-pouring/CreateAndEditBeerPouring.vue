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
                <form @submit.prevent="editmode ? updateBeer() : storeBeer()">
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
                                   v-model="beerForm.names[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentNames(i)">
                                {{ beerErrors.name[`names.${i}`][0] }}
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
                                      v-model="beerForm.descriptions[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentDescription(i)">
                                {{ beerErrors.description[`descriptions.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="video_link">Video link *</label>
                            <input type="url"
                                   class="form-control"
                                   id="video_link"
                                   placeholder="Unesi video link"
                                   :class="{ 'border border-danger': beerErrors.videoLinkErrorPresent}"
                                   v-model="beerForm.videoLink">
                            <small class="text-danger" v-if="beerErrors.videoLinkErrorPresent">
                                {{ beerErrors.videoLink }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-cloak
                             :class="{ 'border border-danger': this.beerErrors.photosErrorPresent}">
                            <input type="file" id="images" multiple accept="image/*"
                                   @change="handleSelects" name="images">
                            <div v-for="(image, i) in this.beerForm.images" class="mt-2">
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
                        <div class="form-group mx-2 mt-2" v-show="this.beerForm.loadPhotos.length > 0">
                            <div v-for="(image, i) in this.beerForm.loadPhotos" class="mt-2">
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
    name: "CreateAndEditBeer",
    components: {
        ImageUploader
    },
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
            beerForm: {
                id: '',
                names: [],
                descriptions: [],
                images: [],
                videoLink: '',
                deletePhotos: [],
                loadPhotos: []
            },
            beerErrors: {
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
            this.beerForm.coverPhoto = output;
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
            this.beerForm.deletePhotos.push(photoUrl);
            this.beerForm.loadPhotos.splice(index, 1);
        },
        createBeer() {
            this.editmode = false;
            this.resetBeerForm();
            this.resetBeerFormErrors();
            $('#create-and-edit-modal').modal();
            $('.img-preview').attr('style', 'display: none !important');
        },
        editBeer(beer) {
            this.editmode = true;
            this.resetBeerForm();
            this.resetBeerFormErrors();
            this.fillBeerForm(beer);
            $('#create-and-edit-modal').modal("show");
            $('.img-preview').attr('style', 'display: none !important');
        },
        storeBeer() {
            this.resetBeerFormErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.beerForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.beerForm.names[i]);
                }

                if (typeof this.beerForm.descriptions[i] === 'undefined' || this.beerForm.descriptions[i] === '<p></p>') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.beerForm.descriptions[i]);
                }
            }
            form.append('video_link', this.beerForm.videoLink);
            this.beerForm.images.forEach(image => {
                form.append('photos[]', image)
            });

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/beer-pouring/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-beer-pouring');
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
        updateBeer() {
            this.resetBeerFormErrors();
            const form = new FormData;
            form.append('id', this.beerForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.beerForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.beerForm.names[i]);
                }

                if (typeof this.beerForm.descriptions[i] === 'undefined') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.beerForm.descriptions[i]);
                }
            }
            form.append('video_link', this.beerForm.videoLink);
            this.beerForm.images.forEach(image => {
                form.append('photos[]', image)
            });

            this.beerForm.deletePhotos.forEach(image => {
                form.append('delete_photos[]', image);
            })

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/beer-pouring/${this.beerForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-beer-pouring');
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
        resetBeerForm() {
            this.beerForm.id = '';
            this.beerForm.names = [];
            this.beerForm.descriptions = [];
            this.beerForm.images = [];
            $('#images').val('');
            this.beerForm.videoLink = '';
            this.beerForm.deletePhotos = [];
            this.beerForm.loadPhotos = [];
        },
        resetBeerFormErrors() {
            this.beerErrors.name = [];
            this.beerErrors.nameErrorPresent = false;
            this.beerErrors.description = [];
            this.beerErrors.descriptionErrorPresent = false;
            this.beerErrors.videoLink = '';
            this.beerErrors.videoLinkErrorPresent = false;
            this.beerErrors.photos = '';
            this.beerErrors.photosErrorPresent = false;
        },
        fillBeerForm(beer) {
            this.beerForm.id = beer.id
            this.beerForm.names[0] = beer.name.me;
            this.beerForm.names[1] = beer.name.en;
            this.beerForm.descriptions[0] = beer.description.me;
            this.beerForm.descriptions[1] = beer.description.en;
            this.beerForm.videoLink = beer.video_link;
            this.beerForm.loadPhotos = beer.photos;
        },
        checkForValidationErrors(errors) {

            let names;
            if (Object.keys(errors).some(function (key) {
                names = key;
                return key.startsWith("names");
            })) {
                this.beerErrors.name = errors;
                this.beerErrors.nameErrorPresent = true;
            }

            let descriptions;
            if (Object.keys(errors).some(function (key) {
                descriptions = key;
                return key.startsWith("descriptions");
            })) {
                this.beerErrors.description = errors;
                this.beerErrors.descriptionErrorPresent = true;
            }

            if (errors.hasOwnProperty('video_link')) {
                this.beerErrors.videoLink = errors["video_link"][0];
                this.beerErrors.videoLinkErrorPresent = true;
            }

            if (errors.hasOwnProperty('photos')) {
                this.beerErrors.photos = errors['photos'][0];
                this.beerErrors.photosErrorPresent = true;
            }
        },
        checkIfErrorPresentNames(i) {
            return this.beerErrors.name !== '' && this.beerErrors.name[`names.${i}`] != null;
        },
        checkIfErrorPresentDescription(i) {
            return this.beerErrors.description !== '' && this.beerErrors.description[`descriptions.${i}`] != null;
        },
        checkImageFileSize(imagFile, limitInMb) {
            return imagFile.size > (limitInMb * 1024 * 1024)
        },
        loadImageFile(e) {
            let imageInput = e.target;
            if (imageInput.files && imageInput.files[0]) {
                if (this.checkImageFileSize(imageInput.files[0], 2)) {
                    this.beerErrors.image = "Maksimalna veličina slike je 2 MB.";
                    this.beerErrors.imageErrorPresent = true;
                } else {
                    this.beerErrors.image = "";
                    this.beerErrors.imageErrorPresent = false;

                    this.beerForm.coverPhoto = imageInput.files[0];
                    const reader = new FileReader();
                    reader.onload = evt => this.loadImage = evt.target.result;
                    reader.readAsDataURL(imageInput.files[0]);
                }
            }
            imageInput.value = "";
        },
        clearData() {
            this.resetBeerFormErrors();
            EventBus.$emit('load-beer-pouring');
        },
        handleSelects(e) {
            this.beerForm.images = [];
            let fileList = Array.prototype.slice.call(e.target.files);
            fileList.forEach(f => {

                if (!f.type.match("image.*")) {
                    return;
                }

                let reader = new FileReader();
                let that = this;

                reader.onload = function (e) {
                    that.beerForm.images.push(e.target.result);
                }
                reader.readAsDataURL(f);
            });
        },
        removeImage(index) {
            Array.from($('#images')[0].files).splice(index, 1);
            this.beerForm.images.splice(index, 1);
        }
    },
    mounted() {
        this.getAllLanguages();
        EventBus.$on('open-create-modal', () => this.createBeer());
        EventBus.$on('open-edit-modal', beer => this.editBeer(beer));
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
