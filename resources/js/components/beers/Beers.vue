<template>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Točena piva</h3>
                <div class="float-right">
                    <form class="form-inline ml-3">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar"
                                   type="search"
                                   placeholder="Pretraga"
                                   aria-label="Search"
                                   v-model="searchKeyword"
                                   @keyup="searchBeers">
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
                    Dodaj točeno pivo
                    <i class="fas fa-plus-circle"></i>
                </button>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center" width="10">ID</th>
                            <th class="text-center">Naziv</th>
                            <th class="text-center">Opis</th>
                            <th class="text-center">Link</th>
                            <th class="text-center">Galerija</th>
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
                        <tr v-for="beer in beers" :key="beer.id">
                            <td class="text-center">{{ beer.id }}</td>
                            <td class="text-center">{{ beer.name.me }}</td>
                            <td class="text-center" v-html="beer.description.me"></td>
                            <td class="text-center">{{ beer.video_link }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info">
                                    <i class="fas fa-images"></i>
                                    Galerija
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" @click="openEditModal(beer)">
                                    <i class="fas fa-edit"></i>
                                    Izmijeni
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" @click="deleteBeer(beer.id)">
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
                            :data="beersPagination"
                            :limit="1"
                            @pagination-change-page="loadSearchedBeers">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
                <pagination v-show="!searchMode"
                            class="mt-2"
                            align="right"
                            show-disabled
                            :data="beersPagination"
                            :limit="1"
                            @pagination-change-page="loadBeers">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
            </div>
        </div>
        <create-and-edit-beer :beer="beers"/>
    </div>
</template>

<script>
import Spinner from "../Spinner";
import {EventBus, swalSuccess, swalError} from "../../main";
import CreateAndEditBeer from "./CreateAndEditBeer";

export default {
    name: "Beers",
    components: {
        Spinner,
        CreateAndEditBeer
    },
    data() {
        return {
            pageIsLoading: true,
            searchMode: false,
            searchKeyword: '',
            beers: [],
            beersPagination: {}
        }
    },
    methods: {
        loadBeers(page = 1) {
            axios.get('/admin/beers')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.beersPagination = response.data[1];
                        this.beers = response.data[1].data;
                        this.pageIsLoading = false;
                    }
                });
        },
        openCreateModal() {
            EventBus.$emit('open-create-modal');
        },
        openEditModal(house) {
            EventBus.$emit('open-edit-modal', house);
        },
        deleteBeer(id) {
            Swal.fire({
                icon: "warning",
                title: "Pažnja",
                text: "Da li ste sigurni da želite da izbrišete točeno pivo?",
                showCancelButton: true,
                confirmButtonColor: "#38c172",
                cancelButtonColor: "#c51f1a",
                confirmButtonText: "Da",
                cancelButtonText: "Ne"
            })
                .then((result) => {
                    if (result.value) {
                        axios.delete(`/admin/beers/${id}`)
                            .then(response => {
                                if (response.data[0] === 'success') {
                                    swalSuccess("Uspješno ste izbrisali točeno pivo!");
                                    EventBus.$emit('load-beers');
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
        loadSearchedBeers(page = 1) {
            axios.get(`/admin/beers/${this.searchKeyword}/search?page=${page}`)
                .then(response => {
                    if (response.data[0] === "success") {
                        this.beersPagination = response.data[1];
                        this.beers = response.data[1].data;
                    }
                });
        },
        searchBeers() {
            if (!(this.searchKeyword === '')) {
                this.searchMode = true;
                axios.get(`/admin/beers/${this.searchKeyword}/search`)
                    .then(response => {
                        if (response.data[0] === "success") {
                            this.beersPagination = response.data[1];
                            this.beers = response.data[1].data;
                        }
                    });
            } else {
                this.searchMode = false;
                this.loadBeers();
            }
        }
    },
    mounted() {
        this.loadBeers();
        this.$emit('loadBreadcrumbLink', {url: '/beers', pageName: 'Točena piva'});
        EventBus.$on('load-beers', () => this.loadBeers());
    }
}

</script>

<style scoped>

</style>
