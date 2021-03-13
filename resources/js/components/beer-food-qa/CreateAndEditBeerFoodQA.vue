<template>
    <div class="modal fade" id="create-and-edit-modal" tabindex="-1" role="dialog"
         aria-labelledby="create-and-edit-modal-label" aria-hidden="true" ref="createAndEditModalRef">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 v-show="editmode" class="modal-title" id="create-and-edit-modal-label">Izmijeni QA</h5>
                    <h5 v-show="!editmode" class="modal-title" id="create-and-edit-modal-label">Dodaj QA</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit.prevent="editmode ? updateBeerQA() : storeBeerQA()">
                    <div class="modal-body">
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'question' + lang.code">Pitanje - {{ lang.code.toUpperCase() }}
                                *</label>
                            <input :id="'question-' + lang.code"
                                   class="form-control"
                                   type="text"
                                   name="questions"
                                   placeholder="Unesite pitanje"
                                   :class="{ 'border border-danger': checkIfErrorPresentQuestions(i) }"
                                   v-model="beerQAForm.questions[i]">
                            <small class="text-danger" v-if="checkIfErrorPresentQuestions(i)">
                                {{ beerQAErrors.questions[`questions.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2" v-for="(lang, i) in languages">
                            <label :for="'answer-' + lang.code">Odgovor - {{
                                    lang.code.toUpperCase()
                                }}*</label>
                            <ckeditor :id="'answer-' + lang.code"
                                      name="answers"
                                      placeholder="Unesite odgovor"
                                      :editor="editor"
                                      :config="editorConfig"
                                      :class="{ 'border border-danger': checkIfErrorPresentAnswers(i) }"
                                      v-model="beerQAForm.answers[i]"></ckeditor>
                            <small class="text-danger" v-if="checkIfErrorPresentAnswers(i)">
                                {{ beerQAErrors.answers[`answers.${i}`][0] }}
                            </small>
                        </div>
                        <hr>
                        <div class="form-group mx-2 mt-2">
                            <label for="role">Kategorija *</label>
                            <select name="role"
                                    id="role"
                                    class="form-control"
                                    :class="{ 'border border-danger': beerQAErrors.categoryErrorPresent }"
                                    v-model="beerQAForm.category">
                                <option value="" disabled selected>Izaberi kategoriju</option>
                                <option v-for="category in categories" :value="category.id">{{
                                        category.name.me
                                    }}
                                </option>
                            </select>
                            <small class="text-danger" v-if="beerQAErrors.categoryErrorPresent">
                                {{ beerQAErrors.category }}
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
    name: "CreateAndEditBeerQa",
    components: {
        ImageUploader
    },
    data() {
        return {
            storeUpdateDisabled: false,
            editmode: true,
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
            beerQAForm: {
                id: '',
                questions: [],
                answers: [],
                category: '',
            },
            beerQAErrors: {
                name: [],
                nameErrorPresent: false,
                description: [],
                descriptionErrorPresent: false,
                category: '',
                categoryErrorPresent: false
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
        getAllCategories() {
            axios.get('/admin/beer-food-qa-categories/all')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.categories = response.data[1];
                    }
                })
        },
        createBeerQA() {
            this.editmode = false;
            this.resetBeerQAForm();
            this.resetBeerQAFormErrors();
            $('#create-and-edit-modal').modal();
        },
        editBeerQA(qa) {
            this.editmode = true;
            this.resetBeerQAForm();
            this.resetBeerQAFormErrors();
            this.fillBeerQAForm(qa);
            $('#create-and-edit-modal').modal("show");
        },
        storeBeerQA() {
            this.resetBeerQAFormErrors();
            const form = new FormData;
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.beerQAForm.questions[i] === 'undefined') {
                    form.append(`questions[${i}]`, '');
                } else {
                    form.append(`questions[${i}]`, this.beerQAForm.questions[i]);
                }

                if (typeof this.beerQAForm.answers[i] === 'undefined' || this.beerQAForm.answers[i] === '<p></p>') {
                    form.append(`answers[${i}]`, '');
                } else {
                    form.append(`answers[${i}]`, this.beerQAForm.answers[i]);
                }
            }
            form.append('category_id', this.beerQAForm.category);

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/beer-food-qa/store`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-beer-food-qa');
                        swalSuccess("QA sekcija je uspješno dodata.");
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
        updateBeerQA() {
            this.resetBeerQAFormErrors();
            const form = new FormData;
            form.append('id', this.beerQAForm.id);
            for (let i = 0; i < this.languages.length; i++) {
                form.append(`languages[${i}]`, this.languages[i]);
                if (typeof this.beerQAForm.questions[i] === 'undefined') {
                    form.append(`questions[${i}]`, '');
                } else {
                    form.append(`questions[${i}]`, this.beerQAForm.questions[i]);
                }

                if (typeof this.beerQAForm.answers[i] === 'undefined') {
                    form.append(`answers[${i}]`, '');
                } else {
                    form.append(`answers[${i}]`, this.beerQAForm.answers[i]);
                }
            }
            form.append('category_id', this.beerQAForm.category);

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/beer-food-qa/${this.beerQAForm.id}/update`, form, config)
                .then(response => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === 'success') {
                        $('#create-and-edit-modal').modal('hide');
                        EventBus.$emit('load-beer-food-qa');
                        swalSuccess("QA sekcija je uspješno izmijenjena.");
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
        resetBeerQAForm() {
            this.beerQAForm.id = '';
            this.beerQAForm.questions = [];
            this.beerQAForm.answers = [];
            this.beerQAForm.category = '';
        },
        resetBeerQAFormErrors() {
            this.beerQAErrors.questions = [];
            this.beerQAErrors.questionsErrorPresent = false;
            this.beerQAErrors.answers = [];
            this.beerQAErrors.answersErrorPresent = false;
            this.beerQAErrors.category = '';
            this.beerQAErrors.categoryErrorPresent = false;
        },
        fillBeerQAForm(beer) {
            this.beerQAForm.id = beer.id
            this.beerQAForm.questions[0] = beer.question.me;
            this.beerQAForm.questions[1] = beer.question.en;
            this.beerQAForm.answers[0] = beer.answer.me;
            this.beerQAForm.answers[1] = beer.answer.en;
            this.beerQAForm.category = beer.category.id;
        },
        checkForValidationErrors(errors) {

            let questions;
            if (Object.keys(errors).some(function (key) {
                questions = key;
                return key.startsWith("questions");
            })) {
                this.beerQAErrors.questions = errors;
                this.beerQAErrors.questionsErrorPresent = true;
            }

            let answers;
            if (Object.keys(errors).some(function (key) {
                answers = key;
                return key.startsWith("answers");
            })) {
                this.beerQAErrors.answers = errors;
                this.beerQAErrors.answersErrorPresent = true;
            }

            if (errors.hasOwnProperty('category_id')) {
                this.beerQAErrors.category = errors['category_id'][0];
                this.beerQAErrors.categoryErrorPresent = true;
            }
        },
        checkIfErrorPresentQuestions(i) {
            return this.beerQAErrors.questions !== '' && this.beerQAErrors.questions[`questions.${i}`] != null;
        },
        checkIfErrorPresentAnswers(i) {
            return this.beerQAErrors.answers !== '' && this.beerQAErrors.answers[`answers.${i}`] != null;
        },
        clearData() {
            this.resetBeerQAFormErrors();
            EventBus.$emit('load-beer-food-qa');
        }
    },
    mounted() {
        this.getAllLanguages();
        this.getAllCategories();
        EventBus.$on('open-create-modal', () => this.createBeerQA());
        EventBus.$on('open-edit-modal', qa => this.editBeerQA(qa));
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
