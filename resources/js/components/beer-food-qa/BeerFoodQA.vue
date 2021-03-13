<template>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">QA sekcija</h3>
                <div class="float-right">
                    <form class="form-inline ml-3">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar"
                                   type="search"
                                   placeholder="Pretraga"
                                   aria-label="Search"
                                   v-model="searchKeyword"
                                   @keyup="searchBeerFoodQA">
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
                    Dodaj QA
                    <i class="fas fa-plus-circle"></i>
                </button>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center" width="10">ID</th>
                            <th class="text-center">Pitanje</th>
                            <th class="text-center">Odgovor</th>
                            <th class="text-center">Kategorija</th>
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
                        <tr v-for="qa in beerFoodQa" :key="qa.id">
                            <td class="text-center">{{ qa.id }}</td>
                            <td class="text-center">{{ qa.question.me }}</td>
                            <td class="text-center" v-html="qa.answer.me"></td>
                            <td class="text-center">{{ qa.category.name.me }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" @click="openEditModal(qa)">
                                    <i class="fas fa-edit"></i>
                                    Izmijeni
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" @click="deleteBeerFoodQA(qa.id)">
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
                            :data="beerFoodQaPagination"
                            :limit="1"
                            @pagination-change-page="loadSearchedBeerFoodQA">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
                <pagination v-show="!searchMode"
                            class="mt-2"
                            align="right"
                            show-disabled
                            :data="beerFoodQaPagination"
                            :limit="1"
                            @pagination-change-page="loadBeerFoodQA">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
            </div>
        </div>
        <create-and-edit-beer-food-q-a :qa="beerFoodQa"/>
    </div>
</template>

<script>
import Spinner from "../Spinner";
import {EventBus, swalError, swalSuccess} from "../../main";
import CreateAndEditBeerFoodQA from "./CreateAndEditBeerFoodQA";

export default {
    name: "BeerFoodQA",
    components: {
        Spinner,
        CreateAndEditBeerFoodQA
    },
    data() {
        return {
            pageIsLoading: true,
            searchMode: false,
            searchKeyword: '',
            beerFoodQa: [],
            beerFoodQaPagination: {}
        }
    },
    methods: {
        loadBeerFoodQA(page = 1) {
            axios.get('/admin/beer-food-qa')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.beerFoodQaPagination = response.data[1];
                        this.beerFoodQa = response.data[1].data;
                        this.pageIsLoading = false;
                    }
                });
        },
        openCreateModal() {
            EventBus.$emit('open-create-modal');
        },
        openEditModal(beerFoodQa) {
            EventBus.$emit('open-edit-modal', beerFoodQa);
        },
        deleteBeerFoodQA(id) {
            Swal.fire({
                icon: "warning",
                title: "Pažnja",
                text: "Da li ste sigurni da želite da izbrišete QA?",
                showCancelButton: true,
                confirmButtonColor: "#38c172",
                cancelButtonColor: "#c51f1a",
                confirmButtonText: "Da",
                cancelButtonText: "Ne"
            })
                .then((result) => {
                    if (result.value) {
                        axios.delete(`/admin/beer-food-qa/${id}`)
                            .then(response => {
                                if (response.data[0] === 'success') {
                                    swalSuccess("Uspješno ste izbrisali QA!");
                                    EventBus.$emit('load-beer-food-qa');
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
        loadSearchedBeerFoodQA(page = 1) {
            axios.get(`/admin/beer-food-qa/${this.searchKeyword}/search?page=${page}`)
                .then(response => {
                    if (response.data[0] === "success") {
                        this.beerFoodQaPagination = response.data[1];
                        this.beerFoodQa = response.data[1].data;
                    }
                });
        },
        searchBeerFoodQA() {
            if (!(this.searchKeyword === '')) {
                this.searchMode = true;
                axios.get(`/admin/beer-food-qa/${this.searchKeyword}/search`)
                    .then(response => {
                        if (response.data[0] === "success") {
                            this.beerFoodQaPagination = response.data[1];
                            this.beerFoodQa = response.data[1].data;
                        }
                    });
            } else {
                this.searchMode = false;
                this.loadBeerFoodQA();
            }
        }
    },
    mounted() {
        this.loadBeerFoodQA();
        this.$emit('loadBreadcrumbLink', {url: '/beer-food-qa', pageName: 'Pivo i hrana - QA sekcija'});
        EventBus.$on('load-beer-food-qa', () => this.loadBeerFoodQA());
    }
}

</script>

<style scoped>

</style>
