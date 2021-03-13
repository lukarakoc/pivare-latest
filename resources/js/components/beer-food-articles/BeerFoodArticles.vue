<template>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Članci</h3>
                <div class="float-right">
                    <form class="form-inline ml-3">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar"
                                   type="search"
                                   placeholder="Pretraga"
                                   aria-label="Search"
                                   v-model="searchKeyword"
                                   @keyup="searchArticles">
                            <div class="input-group-append">
                                <button class="search-button btn btn-navbar border border-muted" @click.prevent="">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body pt-2">
                <button class="btn btn-sm btn-primary float-right mb-2"
                        @click="openCreateModal">
                    Dodaj članak
                    <i class="fas fa-plus-circle"></i>
                </button>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center" width="10">ID</th>
                            <th class="text-center">Naziv</th>
                            <th class="text-center">Tekst</th>
                            <th class="text-center" width="150">Kategorija</th>
                            <th class="text-center" width="150">Izmijeni</th>
                            <th class="text-center" width="150">Izbriši</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="pageIsLoading">
                            <td class="text-center" colspan="9">
                                <spinner/>
                            </td>
                        </tr>
                        <tr v-for="article in articles" :key="article.id">
                            <td class="text-center">{{ article.id }}</td>
                            <td class="text-center">{{ article.title.me }}</td>
                            <td class="text-center" v-html="article.text.me"></td>
                            <td class="text-center">{{ article.category.name.me }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" @click="openEditModal(article)">
                                    <i class="fas fa-edit"></i>
                                    Izmijeni
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" @click="deleteArticle(article.id)">
                                    <i class="fas fa-trash"></i>
                                    Izbriši
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <pagination v-show="searchMode"
                            class="mt-2"
                            align="right"
                            show-disabled
                            :data="articlesPagination"
                            :limit="1"
                            @pagination-change-page="loadSearchedArticles">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
                <pagination v-show="!searchMode"
                            class="mt-2"
                            align="right"
                            show-disabled
                            :data="articlesPagination"
                            :limit="1"
                            @pagination-change-page="loadArticles">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
            </div>
        </div>
        <create-and-edit-beer-food-article :article="articles"/>
    </div>
</template>

<script>
import Spinner from "../Spinner";
import {EventBus, swalError, swalSuccess} from "../../main";
import CreateAndEditBeerFoodArticle from "./CreateAndEditBeerFoodArticle";

export default {
    name: "BeerFoodArticles",
    components: {
        Spinner,
        CreateAndEditBeerFoodArticle
    },
    data() {
        return {
            pageIsLoading: true,
            searchMode: false,
            searchKeyword: '',
            articles: [],
            articlesPagination: {}
        }
    },
    methods: {
        loadArticles(page = 1) {
            axios.get('/admin/beer-food-articles')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.articlesPagination = response.data[1];
                        this.articles = response.data[1].data;
                        this.pageIsLoading = false;
                    }
                });
        },
        openCreateModal() {
            EventBus.$emit('open-create-modal');
        },
        openEditModal(article) {
            EventBus.$emit('open-edit-modal', article);
        },
        deleteArticle(id) {
            Swal.fire({
                icon: "warning",
                title: "Pažnja",
                text: "Da li ste sigurni da želite da izbrišete članak?",
                showCancelButton: true,
                confirmButtonColor: "#38c172",
                cancelButtonColor: "#c51f1a",
                confirmButtonText: "Da",
                cancelButtonText: "Ne"
            })
                .then((result) => {
                    if (result.value) {
                        axios.delete(`/admin/beer-food-articles/${id}`)
                            .then(response => {
                                if (response.data[0] === 'success') {
                                    swalSuccess("Uspješno ste izbrisali članak!");
                                    EventBus.$emit('load-beer-food-articles');
                                }
                            })
                            .catch(error => {
                                if (error.response.status === 422) {
                                    swalError(error.response.data.error)
                                } else {
                                    swalError("Došlo je do greške! Molimo Vas pokušajte ponovo");
                                }
                            })
                    }
                })
        },
        loadSearchedArticles(page = 1) {
            axios.get(`/admin/beer-food-articles/${this.searchKeyword}/search?page=${page}`)
                .then(response => {
                    if (response.data[0] === "success") {
                        this.articlesPagination = response.data[1];
                        this.articles = response.data[1].data;
                    }
                });
        },
        searchArticles() {
            if (!(this.searchKeyword === '')) {
                this.searchMode = true;
                axios.get(`/admin/beer-food-articles/${this.searchKeyword}/search`)
                    .then(response => {
                        if (response.data[0] === "success") {
                            this.articlesPagination = response.data[1];
                            this.articles = response.data[1].data;
                        }
                    });
            } else {
                this.searchMode = false;
                this.loadArticles();
            }
        }
    },
    mounted() {
        this.loadArticles();
        this.$emit('loadBreadcrumbLink', {
            url: '/beer-food-articles',
            pageName: 'Pivo i hrana - članci'
        });
        EventBus.$on('load-beer-food-articles', () => this.loadArticles());
    }
}

</script>

<style scoped>

</style>
