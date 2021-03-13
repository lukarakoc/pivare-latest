<template>
    <div class="modal fade" id="create-and-edit-modal" tabindex="-1" role="dialog"
         aria-labelledby="create-and-edit-modal-label" aria-hidden="true" ref="createAndEditModalRef">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 v-show="editmode" class="modal-title" id="create-and-edit-modal-label">Izmijeni članak</h5>
                    <h5 v-show="!editmode" class="modal-title" id="create-and-edit-modal-label">Dodaj članak</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode ? updateArticle() : storeArticle()">
                    <div class="modal-body">
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'title' + lang.code">Naslov članka - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'title' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="title"
                                   placeholder="Unesite naslov članka"
                                   :class="{ 'border border-danger': checkIfErrorPresentNames(i) }"
                                   v-model="articleForm.titles[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentNames(i)">
                                {{ articleErrors.title[`titles.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'text-' + lang.code">Tekst članka - {{
                                    lang.code.toUpperCase()
                                }} *</label>
                            <ckeditor :id="'text-' + lang.code"
                                      name="text"
                                      placeholder="Unesite tekst članka"
                                      :editor="editor"
                                      :config="editorConfig"
                                      :class="{ 'border border-danger': checkIfErrorPresentDescription(i) }"
                                      v-model="articleForm.texts[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentDescription(i)">
                                {{ articleErrors.text[`texts.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="role">Kategorija *</label>
                            <select name="role"
                                    id="role"
                                    class="form-control"
                                    :class="{ 'border border-danger': articleErrors.categoryErrorPresent }"
                                    v-model="articleForm.category">
                                <option value="" disabled selected>Izaberi kategoriju</option>
                                <option v-for="category in categories" :value="category.id">{{
                                        category.name.me
                                    }}
                                </option>
                            </select>
                            <small class="text-danger" v-if="articleErrors.categoryErrorPresent">
                                {{ articleErrors.category }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="video_link">Video link *</label>
                            <input type="url"
                                   class="form-control"
                                   id="video_link"
                                   placeholder="Unesi video link"
                                   :class="{ 'border border-danger': articleErrors.videoLinkErrorPresent}"
                                   v-model="articleForm.videoLink">
                            <small class="text-danger" v-if="articleErrors.videoLinkErrorPresent">
                                {{ articleErrors.videoLink }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-cloak
                             :class="{ 'border border-danger': this.articleErrors.photosErrorPresent}">
                            <input type="file" id="images" multiple accept="image/*"
                                   @change="handleSelects" name="images">
                            <small class="text-danger" v-if="articleErrors.photosErrorPresent">
                                {{ articleErrors.photos }}
                            </small>                            <div v-for="(image, i) in this.articleForm.images" class="mt-2">
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
                        <div class="form-group mx-2 mt-2" v-show="this.articleForm.loadPhotos.length > 0">
                            <div v-for="(image, i) in this.articleForm.loadPhotos" class="mt-2">
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
    name: "CreateAndEditBeerFoodArticle.vue",
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
            categories: [],
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
            articleForm: {
                id: '',
                titles: [],
                texts: [],
                images: [],
                category: '',
                videoLink: '',
                deletePhotos: [],
                loadPhotos: []
            },
            articleErrors: {
                title: [],
                titleErrorPresent: false,
                text: [],
                textErrorPresent: false,
                videoLink: '',
                videoLinkErrorPresent: false,
                category: '',
                categoryErrorPresent: false,
                photos: '',
                photosErrorPresent: false
            }
        }
    },
    methods: {
        setImage(output) {
            this.loadImage = null;
            this.articleForm.coverPhoto = output;
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
        getAllCategories() {
            axios.get('/admin/beer-food-categories/all')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.categories = response.data[1];
                    }
                })
        },
        addToDeleted(photoUrl, index) {
            this.articleForm.deletePhotos.push(photoUrl);
            this.articleForm.loadPhotos.splice(index, 1);
        },
        createArticle() {
            this.editmode = false;
            this.resetArticleForm();
            this.resetArticleFormErrors();
            $('#create-and-edit-modal').modal();
            $('.img-preview').attr('style', 'display: none !important');
        },
        editArticle(article) {
            this.editmode = true;
            this.resetArticleForm();
            this.resetArticleFormErrors();
            this.fillArticleForm(article);
            $('#create-and-edit-modal').modal("show");
            $('.img-preview').attr('style', 'display: none !important');
        },
        storeArticle() {
            this.resetArticleFormErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.articleForm.titles[i] === 'undefined') {
                    form.append(`titles[${i}]`, '');
                } else {
                    form.append(`titles[${i}]`, this.articleForm.titles[i]);
                }

                if (typeof this.articleForm.texts[i] === 'undefined' || this.articleForm.texts[i] === '<p></p>') {
                    form.append(`texts[${i}]`, '');
                } else {
                    form.append(`texts[${i}]`, this.articleForm.texts[i]);
                }
            }
            form.append('video_link', this.articleForm.videoLink);
            if (this.articleForm.images.length > 0) {
                this.articleForm.images.forEach(image => {
                    form.append('photos[]', image)
                });
            }

            form.append('category_id', this.articleForm.category);

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/beer-food-articles/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-beer-food-articles');
                        swalSuccess("Članak je uspješno dodat.");
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
        updateArticle() {
            this.resetArticleFormErrors();
            const form = new FormData;
            form.append('id', this.articleForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.articleForm.titles[i] === 'undefined') {
                    form.append(`titles[${i}]`, '');
                } else {
                    form.append(`titles[${i}]`, this.articleForm.titles[i]);
                }

                if (typeof this.articleForm.texts[i] === 'undefined') {
                    form.append(`texts[${i}]`, '');
                } else {
                    form.append(`texts[${i}]`, this.articleForm.texts[i]);
                }
            }
            form.append('video_link', this.articleForm.videoLink);
            if (this.articleForm.images.length > 0) {
                this.articleForm.images.forEach(image => {
                    form.append('photos[]', image)
                });
            }

            this.articleForm.deletePhotos.forEach(image => {
                form.append('delete_photos[]', image);
            })
            form.append('category_id', this.articleForm.category);


            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/beer-food-articles/${this.articleForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-beer-food-articles');
                        swalSuccess("Članak je uspješno izmijenjen.");
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
        resetArticleForm() {
            this.articleForm.id = '';
            this.articleForm.titles = [];
            this.articleForm.texts = [];
            this.articleForm.images = [];
            $('#images').val('');
            this.articleForm.videoLink = '';
            this.articleForm.category = '';
            this.articleForm.deletePhotos = [];
            this.articleForm.loadPhotos = [];
        },
        resetArticleFormErrors() {
            this.articleErrors.title = [];
            this.articleErrors.titleErrorPresent = false;
            this.articleErrors.text = [];
            this.articleErrors.textErrorPresent = false;
            this.articleErrors.videoLink = '';
            this.articleErrors.videoLinkErrorPresent = false;
            this.articleErrors.photos = '';
            this.articleErrors.photosErrorPresent = false;
            this.articleErrors.category = '';
            this.articleErrors.categoryErrorPresent = false;
        },
        fillArticleForm(article) {
            this.articleForm.id = article.id
            this.articleForm.titles[0] = article.title.me;
            this.articleForm.titles[1] = article.title.en;
            this.articleForm.texts[0] = article.text.me;
            this.articleForm.texts[1] = article.text.en;
            this.articleForm.videoLink = article.video_link;
            this.articleForm.category = article.category.id;
            this.articleForm.loadPhotos = article.photos;
        },
        checkForValidationErrors(errors) {

            let titles;
            if (Object.keys(errors).some(function (key) {
                titles = key;
                return key.startsWith("titles");
            })) {
                this.articleErrors.title = errors;
                this.articleErrors.titleErrorPresent = true;
            }

            let texts;
            if (Object.keys(errors).some(function (key) {
                texts = key;
                return key.startsWith("texts");
            })) {
                this.articleErrors.text = errors;
                this.articleErrors.textErrorPresent = true;
            }

            if (errors.hasOwnProperty('video_link')) {
                this.articleErrors.videoLink = errors["video_link"][0];
                this.articleErrors.videoLinkErrorPresent = true;
            }

            if (errors.hasOwnProperty('category_id')) {
                this.articleErrors.category = errors["category_id"][0];
                this.articleErrors.categoryErrorPresent = true;
            }

            if (errors.hasOwnProperty('photos')) {
                this.articleErrors.photos = errors['photos'][0];
                this.articleErrors.photosErrorPresent = true;
            }
        },
        checkIfErrorPresentNames(i) {
            return this.articleErrors.title !== '' && this.articleErrors.title[`titles.${i}`] != null;
        },
        checkIfErrorPresentDescription(i) {
            return this.articleErrors.text !== '' && this.articleErrors.text[`texts.${i}`] != null;
        },
        checkImageFileSize(imagFile, limitInMb) {
            return imagFile.size > (limitInMb * 1024 * 1024)
        },
        loadImageFile(e) {
            let imageInput = e.target;
            if (imageInput.files && imageInput.files[0]) {
                if (this.checkImageFileSize(imageInput.files[0], 2)) {
                    this.articleErrors.image = "Maksimalna veličina slike je 2 MB.";
                    this.articleErrors.imageErrorPresent = true;
                } else {
                    this.articleErrors.image = "";
                    this.articleErrors.imageErrorPresent = false;

                    this.articleForm.coverPhoto = imageInput.files[0];
                    const reader = new FileReader();
                    reader.onload = evt => this.loadImage = evt.target.result;
                    reader.readAsDataURL(imageInput.files[0]);
                }
            }
            imageInput.value = "";
        },
        clearData() {
            this.resetArticleFormErrors();
            EventBus.$emit('load-beer-food-articles');
        },
        handleSelects(e) {
            this.articleForm.images = [];
            let fileList = Array.prototype.slice.call(e.target.files);
            fileList.forEach(f => {

                if (!f.type.match("image.*")) {
                    return;
                }

                let reader = new FileReader();
                let that = this;

                reader.onload = function (e) {
                    that.articleForm.images.push(e.target.result);
                }
                reader.readAsDataURL(f);
            });
        },
        removeImage(index) {
            Array.from($('#images')[0].files).splice(index, 1);
            this.articleForm.images.splice(index, 1);
        }
    },
    mounted() {
        this.getAllLanguages();
        this.getAllCategories();
        EventBus.$on('open-create-modal', () => this.createArticle());
        EventBus.$on('open-edit-modal', article => this.editArticle(article));
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
