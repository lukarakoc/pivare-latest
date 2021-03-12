<template>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kategorije</h3>
                <div class="float-right">
                    <form class="form-inline ml-3">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar"
                                   type="search"
                                   placeholder="Pretraga"
                                   aria-label="Search"
                                   v-model="searchKeyword"
                                   @keyup="searchBeerFoodCategories">
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
                    Dodaj kategoriju
                    <i class="fas fa-plus-circle"></i>
                </button>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center" width="10">ID</th>
                            <th class="text-center">Naziv</th>
                            <th class="text-center">Opis</th>
                            <th class="text-center">Izmijeni</th>
                            <th class="text-center">Izbriši</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="pageIsLoading">
                            <td class="text-center" colspan="9">
                                <spinner/>
                            </td>
                        </tr>
                        <tr v-for="beer in beerFoodCategories" :key="beer.id">
                            <td class="text-center">{{ beer.id }}</td>
                            <td class="text-center">{{ beer.name.me }}</td>
                            <td class="text-center" v-html="beer.description.me"></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" @click="openEditModal(beer)">
                                    <i class="fas fa-edit"></i>
                                    Izmijeni
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" @click="deleteBeerFoodCategory(beer.id)">
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
                            :data="beersFoodCategoriesPagination"
                            :limit="1"
                            @pagination-change-page="loadSearchedBeerFoodCategories">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
                <pagination v-show="!searchMode"
                            class="mt-2"
                            align="right"
                            show-disabled
                            :data="beersFoodCategoriesPagination"
                            :limit="1"
                            @pagination-change-page="loadBeerFoodCategories">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
            </div>
        </div>
        <create-and-edit-beer-food-category :beer="beerFoodCategories"/>
    </div>
</template>

<script>
import Spinner from "../Spinner";
import {EventBus, swalError, swalSuccess} from "../../main";
import CreateAndEditBeerFoodCategory from './CreateAndEditBeerFoodCategory';

export default {
    name: "BeerFoodCategories",
    components: {
        Spinner,
        CreateAndEditBeerFoodCategory
    },
    data() {
        return {
            pageIsLoading: true,
            searchMode: false,
            searchKeyword: '',
            beerFoodCategories: [],
            beersFoodCategoriesPagination: {}
        }
    },
    methods: {
        loadBeerFoodCategories(page = 1) {
            axios.get('/admin/beer-food-categories')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.beersFoodCategoriesPagination = response.data[1];
                        this.beerFoodCategories = response.data[1].data;
                        this.pageIsLoading = false;
                    }
                });
        },
        openCreateModal() {
            EventBus.$emit('open-create-modal');
        },
        openEditModal(beerFoodCategory) {
            EventBus.$emit('open-edit-modal', beerFoodCategory);
        },
        deleteBeerFoodCategory(id) {
            Swal.fire({
                icon: "warning",
                title: "Pažnja",
                text: "Da li ste sigurni da želite da izbrišete kategoriju?",
                showCancelButton: true,
                confirmButtonColor: "#38c172",
                cancelButtonColor: "#c51f1a",
                confirmButtonText: "Da",
                cancelButtonText: "Ne"
            })
                .then((result) => {
                    if (result.value) {
                        axios.delete(`/admin/beer-food-categories/${id}`)
                            .then(response => {
                                if (response.data[0] === 'success') {
                                    swalSuccess("Uspješno ste izbrisali kategoriju!");
                                    EventBus.$emit('load-beer-food-categories');
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
        loadSearchedBeerFoodCategories(page = 1) {
            axios.get(`/admin/beer-food-categories/${this.searchKeyword}/search?page=${page}`)
                .then(response => {
                    if (response.data[0] === "success") {
                        this.beersFoodCategoriesPagination = response.data[1];
                        this.beerFoodCategories = response.data[1].data;
                    }
                });
        },
        searchBeerFoodCategories() {
            if (!(this.searchKeyword === '')) {
                this.searchMode = true;
                axios.get(`/admin/beer-food-categories/${this.searchKeyword}/search`)
                    .then(response => {
                        if (response.data[0] === "success") {
                            this.beersFoodCategoriesPagination = response.data[1];
                            this.beerFoodCategories = response.data[1].data;
                        }
                    });
            } else {
                this.searchMode = false;
                this.loadBeerFoodCategories();
            }
        }
    },
    mounted() {
        this.loadBeerFoodCategories();
        this.$emit('loadBreadcrumbLink', {url: '/beer-food-categories', pageName: 'Pivo i hrana - kategorije'});
        EventBus.$on('load-beer-food-categories', () => this.loadBeerFoodCategories());
    }
}

</script>

<style scoped>

</style>
