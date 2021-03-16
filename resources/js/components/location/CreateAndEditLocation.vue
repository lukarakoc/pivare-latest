<template>
    <div class="modal fade" id="create-and-edit-modal" tabindex="-1" role="dialog"
         aria-labelledby="create-and-edit-modal-label" aria-hidden="true" ref="createAndEditModalRef">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 v-show="editmode" class="modal-title" id="create-and-edit-modal-label">Izmijeni lokaciju</h5>
                    <h5 v-show="!editmode" class="modal-title" id="create-and-edit-modal-label">Dodaj lokaciju</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode ? updateLocation() : storeLocation()">
                    <div class="modal-body">
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'name' + lang.code">Naziv lokacije - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'name' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="name"
                                   placeholder="Unesite naziv"
                                   :class="{ 'border border-danger': checkIfErrorPresentNames(i) }"
                                   v-model="locationForm.names[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentNames(i)">
                                {{ locationErrors.name[`names.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'description-' + lang.code">Opis lokacije - {{
                                    lang.code.toUpperCase()
                                }}*</label>
                            <ckeditor :id="'description-' + lang.code"
                                      name="text"
                                      placeholder="Unesite opis"
                                      :editor="editor"
                                      :config="editorConfig"
                                      :class="{ 'border border-danger': checkIfErrorPresentDescription(i) }"
                                      v-model="locationForm.descriptions[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentDescription(i)">
                                {{ locationErrors.description[`descriptions.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'address' + lang.code">Adresa lokacije - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'address' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="address"
                                   placeholder="Unesite adresu"
                                   :class="{ 'border border-danger': checkIfErrorPresentAddresses(i) }"
                                   v-model="locationForm.address[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentAddresses(i)">
                                {{ locationErrors.address[`names.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="role">Kategorija *</label>
                            <select name="role"
                                    id="role"
                                    class="form-control"
                                    :class="{ 'border border-danger': locationErrors.categoryErrorPresent }"
                                    v-model="locationForm.category">
                                <option value="" disabled selected>Izaberi ulogu</option>
                                <option v-for="category in categories" :value="category.id">{{
                                        category.name.me
                                    }}
                                </option>
                            </select>
                            <small class="text-danger" v-if="locationErrors.categoryErrorPresent">
                                {{ locationErrors.category }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="owner">Vlasnik *</label>
                            <select name="owner"
                                    id="owner"
                                    class="form-control"
                                    :class="{ 'border border-danger': locationErrors.ownerErrorPresent }"
                                    v-model="locationForm.owner">
                                <option value="" disabled selected>Izaberi ulogu</option>
                                <option v-for="owner in owners" :value="owner.id">{{
                                        owner.name
                                    }}
                                </option>
                            </select>
                            <small class="text-danger" v-if="locationErrors.ownerErrorPresent">
                                {{ locationErrors.owner }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <div class="text-center">
                                <img class="rounded mx-auto"
                                     alt="Image"
                                     style="max-width: 300px;"
                                     :src="loadImage"
                                     v-if="loadImage">
                            </div>
                            <div class="form-group mx-2 mt-2">
                                <label for="">Logotip *</label>
                                <br>
                                <input type="file" id="image" multiple accept="image/*"
                                       @change="handleSelectsSingle" name="image"
                                       :class="{ 'border border-danger': this.locationErrors.photosErrorPresent}">
                            </div>
                            <small class="text-danger" v-if="locationErrors.imageErrorPresent">
                                {{ locationErrors.image }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2"
                             :style="{'border border-danger' : locationErrors.latitudeErrorPresent || locationErrors.longitudeErrorPresent}">
                            <label for="map">Lokacija na mapi</label>
                            <map-leaflet id="map"></map-leaflet>
                            <small class="text-danger"
                                   v-if="locationErrors.latitudeErrorPresent || locationErrors.longitudeErrorPresent">
                                Morate označiti lokaciju na mapi
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-cloak
                             :class="{ 'border border-danger': this.locationErrors.photosErrorPresent}">
                            <input type="file" id="images" multiple accept="image/*"
                                   @change="handleSelects" name="images">
                            <small class="text-danger" v-if="locationErrors.photosErrorPresent">
                                {{ locationErrors.photos }}
                            </small>
                            <div v-for="(image, i) in this.locationForm.images" class="mt-2">
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
                        <div class="form-group mx-2 mt-2" v-show="this.locationForm.loadPhotos.length > 0">
                            <div v-for="(image, i) in this.locationForm.loadPhotos" class="mt-2">
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

import {checkIfNotEmpty, EventBus, swalSuccess} from "../../main";
import ImageUploader from "vue-image-upload-resize";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import MapLeaflet from "../map/MapLeaflet";

export default {
    name: "CreateAndEditBeer",
    components: {
        ImageUploader,
        MapLeaflet
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
            owners: [],
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
            locationForm: {
                id: '',
                names: [],
                descriptions: [],
                address: [],
                images: [],
                category: '',
                image: '',
                latitude: '',
                longitude: '',
                owner: '',
                deletePhotos: [],
                loadPhotos: []
            },
            locationErrors: {
                name: [],
                nameErrorPresent: false,
                description: [],
                descriptionErrorPresent: false,
                videoLink: '',
                videoLinkErrorPresent: false,
                photos: '',
                photosErrorPresent: false,
                images: '',
                imagesErrorPresent: false,
                image: '',
                imageErrorPresent: false,
                address: [],
                addressErrorPresent: false,
                latitude: [],
                latitudeErrorPresent: false,
                longitude: [],
                longitudeErrorPresent: false,
                category: [],
                categoryErrorPresent: false,
                owner: '',
                ownerErrorPresent: false
            }
        }
    },
    methods: {
        setImage(output) {
            this.loadImage = null;
            this.locationForm.image = output;
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
        getOwners() {
            axios.get('/admin/users/owners')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.owners = response.data[1];
                    }
                })
        },
        loadCategories() {
            axios.get('/admin/location-categories/all')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.categories = response.data[1];
                    }
                })
        },
        addToDeleted(photoUrl, index) {
            this.locationForm.deletePhotos.push(photoUrl);
            this.locationForm.loadPhotos.splice(index, 1);
        },
        createLocation() {
            this.editmode = false;
            this.resetLocationForm();
            this.resetLocationFormErrors();
            $('#create-and-edit-modal').modal();
            $('.img-preview').attr('style', 'display: none !important');
        },
        editLocation(location) {
            this.editmode = true;
            this.resetLocationForm();
            this.resetLocationFormErrors();
            this.fillForm(location);
            $('#create-and-edit-modal').modal("show");
            $('.img-preview').attr('style', 'display: none !important');
        },
        storeLocation() {
            this.resetLocationFormErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.locationForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.locationForm.names[i]);
                }

                if (typeof this.locationForm.descriptions[i] === 'undefined' || this.locationForm.descriptions[i] === '<p></p>') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.locationForm.descriptions[i]);
                }

                if (typeof this.locationForm.address[i] === 'undefined' || this.locationForm.address[i] === '<p></p>') {
                    form.append(`addresses[${i}]`, '');
                } else {
                    form.append(`addresses[${i}]`, this.locationForm.address[i]);
                }
            }
            form.append('category_id', this.locationForm.category);
            form.append('owner', this.locationForm.owner);
            form.append('latitude', this.locationForm.latitude);
            form.append('longitude', this.locationForm.longitude);
            this.locationForm.images.forEach(image => {
                form.append('photos[]', image)
            });
            if (checkIfNotEmpty(this.locationForm.image)) {
                form.append('logo', this.locationForm.image);
            }

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/locations/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-locations');
                        swalSuccess("Lokacija je uspješno dodata.");
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
        updateLocation() {
            this.resetLocationFormErrors();
            const form = new FormData;
            form.append('id', this.locationForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.locationForm.names[i] === 'undefined') {
                    form.append(`names[${i}]`, '');
                } else {
                    form.append(`names[${i}]`, this.locationForm.names[i]);
                }

                if (typeof this.locationForm.descriptions[i] === 'undefined' || this.locationForm.descriptions[i] === '<p></p>') {
                    form.append(`descriptions[${i}]`, '');
                } else {
                    form.append(`descriptions[${i}]`, this.locationForm.descriptions[i]);
                }

                if (typeof this.locationForm.address[i] === 'undefined' || this.locationForm.address[i] === '<p></p>') {
                    form.append(`addresses[${i}]`, '');
                } else {
                    form.append(`addresses[${i}]`, this.locationForm.address[i]);
                }
            }
            form.append('category_id', this.locationForm.category);
            form.append('owner', this.locationForm.owner);
            form.append('latitude', this.locationForm.latitude);
            form.append('longitude', this.locationForm.longitude);
            this.locationForm.images.forEach(image => {
                form.append('photos[]', image)
            });
            if (checkIfNotEmpty(this.locationForm.image)) {
                form.append('logo', this.locationForm.image);
            }

            this.locationForm.deletePhotos.forEach(image => {
                form.append('delete_photos[]', image);
            })

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/locations/${this.locationForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-locations');
                        swalSuccess("Lokacija je uspješno izmijenjena.");
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
        fillMarkersData(data) {
            this.locationForm.latitude = data.lat;
            this.locationForm.longitude = data.lng;
        },
        resetLocationForm() {
            this.locationForm.id = '';
            this.locationForm.names = [];
            this.locationForm.descriptions = [];
            this.locationForm.address = [];
            this.locationForm.images = [];
            this.locationForm.latitude = '';
            this.locationForm.longitude = '';
            this.locationForm.owner = '';
            $('#images').val('');
            this.locationForm.category = '';
            this.locationForm.image = '';
            this.locationForm.deletePhotos = [];
            this.locationForm.loadPhotos = [];
        },
        resetLocationFormErrors() {
            this.locationErrors.name = [];
            this.locationErrors.nameErrorPresent = false;
            this.locationErrors.description = [];
            this.locationErrors.descriptionErrorPresent = false;
            this.locationErrors.videoLink = '';
            this.locationErrors.videoLinkErrorPresent = false;
            this.locationErrors.photos = '';
            this.locationErrors.photosErrorPresent = false;
            this.locationErrors.images = '';
            this.locationErrors.imagesErrorPresent = false;
            this.locationErrors.image = '';
            this.locationErrors.imageErrorPresent = false;
            this.locationErrors.address = [];
            this.locationErrors.addressErrorPresent = false;
            this.locationErrors.latitude = [];
            this.locationErrors.latitudeErrorPresent = false;
            this.locationErrors.longitude = [];
            this.locationErrors.longitudeErrorPresent = false;
            this.locationErrors.category = [];
            this.locationErrors.categoryErrorPresent = false;
            this.loadImage = '';
            this.locationErrors.owner = '';
            this.locationErrors.ownerErrorPresent = false;
            $('#images').val('');
            $('#image').val('');
        },
        fillForm(location) {
            this.locationForm.id = location.id
            this.locationForm.names[0] = location.name.me;
            this.locationForm.names[1] = location.name.en;
            this.locationForm.descriptions[0] = location.description.me;
            this.locationForm.descriptions[1] = location.description.en;
            this.locationForm.address[0] = location.address.me;
            this.locationForm.address[1] = location.address.en;
            this.locationForm.category = location.location_category_id;
            this.locationForm.latitude = location.latitude;
            this.locationForm.longitude = location.latitude;
            this.loadImage = location.logo;
            this.locationForm.loadPhotos = location.photos;
            this.locationForm.owner = location.owner_id;
        },
        checkForValidationErrors(errors) {

            let names;
            if (Object.keys(errors).some(function (key) {
                names = key;
                return key.startsWith("names");
            })) {
                this.locationErrors.name = errors;
                this.locationErrors.nameErrorPresent = true;
            }

            let descriptions;
            if (Object.keys(errors).some(function (key) {
                descriptions = key;
                return key.startsWith("descriptions");
            })) {
                this.locationErrors.description = errors;
                this.locationErrors.descriptionErrorPresent = true;
            }

            let addresses;
            if (Object.keys(errors).some(function (key) {
                addresses = key;
                return key.startsWith("addresses");
            })) {
                this.locationErrors.address = errors;
                this.locationErrors.addressErrorPresent = true;
            }

            if (errors.hasOwnProperty('category_id')) {
                this.locationErrors.category = errors["category_id"][0];
                this.locationErrors.categoryErrorPresent = true;
            }

            if (errors.hasOwnProperty('latitude')) {
                this.locationErrors.latitude = errors["latitude"][0];
                this.locationErrors.latitudeErrorPresent = true;
            }

            if (errors.hasOwnProperty('longitude')) {
                this.locationErrors.longitude = errors["longitude"][0];
                this.locationErrors.longitudeErrorPresent = true;
            }

            if (errors.hasOwnProperty('logo')) {
                this.locationErrors.image = errors["logo"][0];
                this.locationErrors.imageErrorPresent = true;
            }

            if (errors.hasOwnProperty('owner')) {
                this.locationErrors.owner = errors["owner"][0];
                this.locationErrors.ownerErrorPresent = true;
            }

            if (errors.hasOwnProperty('photos')) {
                this.locationErrors.photos = errors['photos'][0];
                this.locationErrors.photosErrorPresent = true;
            }
        },
        checkIfErrorPresentNames(i) {
            return this.locationErrors.name !== '' && this.locationErrors.name[`names.${i}`] != null;
        },
        checkIfErrorPresentAddresses(i) {
            return this.locationErrors.address !== '' && this.locationErrors.address[`addresses.${i}`] != null;
        },
        checkIfErrorPresentDescription(i) {
            return this.locationErrors.description !== '' && this.locationErrors.description[`descriptions.${i}`] != null;
        },
        checkImageFileSize(imagFile, limitInMb) {
            return imagFile.size > (limitInMb * 1024 * 1024)
        },
        loadImageFile(e) {
            let imageInput = e.target;
            if (imageInput.files && imageInput.files[0]) {
                if (this.checkImageFileSize(imageInput.files[0], 2)) {
                    this.locationErrors.image = "Maksimalna veličina slike je 2 MB.";
                    this.locationErrors.imageErrorPresent = true;
                } else {
                    this.locationErrors.image = "";
                    this.locationErrors.imageErrorPresent = false;

                    this.locationForm.image = imageInput.files[0];
                    const reader = new FileReader();
                    reader.onload = evt => this.loadImage = evt.target.result;
                    reader.readAsDataURL(imageInput.files[0]);
                }
            }
            imageInput.value = "";
        },
        clearData() {
            this.resetLocationFormErrors();
            EventBus.$emit('load-locations');
        },
        handleSelects(e) {
            this.locationForm.images = [];
            let fileList = Array.prototype.slice.call(e.target.files);
            fileList.forEach(f => {

                if (!f.type.match("image.*")) {
                    return;
                }

                let reader = new FileReader();
                let that = this;

                reader.onload = function (e) {
                    that.locationForm.images.push(e.target.result);
                }
                reader.readAsDataURL(f);
            });
        },
        handleSelectsSingle(e) {
            this.locationForm.image = '';
            let fileList = Array.prototype.slice.call(e.target.files);
            fileList.forEach(f => {

                if (!f.type.match("image.*")) {
                    return;
                }

                let reader = new FileReader();
                let that = this;

                reader.onload = function (e) {
                    that.locationForm.image = e.target.result;
                    that.loadImage = e.target.result;
                }
                reader.readAsDataURL(f);
            });
        },
        removeImage(index) {
            Array.from($('#images')[0].files).splice(index, 1);
            this.locationForm.images.splice(index, 1);
        }
    },
    mounted() {
        this.getAllLanguages();
        this.loadCategories();
        this.getOwners();
        EventBus.$on('open-create-modal', () => this.createLocation());
        EventBus.$on('open-edit-modal', location => this.editLocation(location));
        $(this.$refs.createAndEditModalRef).on("hidden.bs.modal", this.clearData);
        EventBus.$on('marker', data => this.fillMarkersData(data));
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
