<template>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tutorijal o održavanju opreme</h3>
                <div class="float-right">
                    <form class="form-inline ml-3">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar"
                                   type="search"
                                   placeholder="Pretraga"
                                   aria-label="Search"
                                   v-model="searchKeyword"
                                   @keyup="searchEMSections">
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
                    Dodaj sekciju
                    <i class="fas fa-plus-circle"></i>
                </button>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center" width="10">ID</th>
                            <th class="text-center">Naziv</th>
                            <th class="text-center">Opis</th>
                            <th class="text-center">Koraci</th>
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
                        <tr v-for="section in emSections" :key="section.id">
                            <td class="text-center">{{ section.id }}</td>
                            <td class="text-center">{{ section.name.me }}</td>
                            <td class="text-center" v-html="section.description.me"></td>
                            <td class="text-center">
                                <router-link :to="{ name: 'EMSteps', params: { id: section.id }}">
                                    <i class="fas fa-shoe-prints"></i>
                                    Koraci
                                </router-link>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" @click="openEditModal(section)">
                                    <i class="fas fa-edit"></i>
                                    Izmijeni
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" @click="deleteEMSection(section.id)">
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
                            :data="emSectionsPagination"
                            :limit="1"
                            @pagination-change-page="loadSearchedEMSections">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
                <pagination v-show="!searchMode"
                            class="mt-2"
                            align="right"
                            show-disabled
                            :data="emSectionsPagination"
                            :limit="1"
                            @pagination-change-page="loadEMSections">
                    <span slot="prev-nav">Prethodna</span>
                    <span slot="next-nav">Sledeća</span>
                </pagination>
            </div>
        </div>
        <create-and-edit-e-m-section :beer="beers"/>
    </div>
</template>

<script>
import Spinner from "../Spinner";
import {EventBus, swalError, swalSuccess} from "../../main";
import CreateAndEditEMSection from "./CreateAndEditEMSection";

export default {
    name: "BeerQACategories",
    components: {
        Spinner,
        CreateAndEditEMSection
    },
    data() {
        return {
            pageIsLoading: true,
            searchMode: false,
            searchKeyword: '',
            emSections: [],
            emSectionsPagination: {}
        }
    },
    methods: {
        loadEMSections(page = 1) {
            axios.get('/admin/equipment-maintenance-sections')
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.emSectionsPagination = response.data[1];
                        this.emSections = response.data[1].data;
                        this.pageIsLoading = false;
                    }
                });
        },
        openCreateModal() {
            EventBus.$emit('open-create-modal');
        },
        openEditModal(section) {
            EventBus.$emit('open-edit-modal', section);
        },
        deleteEMSection(id) {
            Swal.fire({
                icon: "warning",
                title: "Pažnja",
                text: "Da li ste sigurni da želite da izbrišete sekciju?",
                showCancelButton: true,
                confirmButtonColor: "#38c172",
                cancelButtonColor: "#c51f1a",
                confirmButtonText: "Da",
                cancelButtonText: "Ne"
            })
                .then((result) => {
                    if (result.value) {
                        axios.delete(`/admin/equipment-maintenance-sections/${id}`)
                            .then(response => {
                                if (response.data[0] === 'success') {
                                    swalSuccess("Uspješno ste izbrisali sekciju!");
                                    EventBus.$emit('load-em-sections');
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
        loadSearchedEMSections(page = 1) {
            axios.get(`/admin/equipment-maintenance-sections/${this.searchKeyword}/search?page=${page}`)
                .then(response => {
                    if (response.data[0] === "success") {
                        this.emSectionsPagination = response.data[1];
                        this.emSections = response.data[1].data;
                    }
                });
        },
        searchEMSections() {
            if (!(this.searchKeyword === '')) {
                this.searchMode = true;
                axios.get(`/admin/equipment-maintenance-sections/${this.searchKeyword}/search`)
                    .then(response => {
                        if (response.data[0] === "success") {
                            this.emSectionsPagination = response.data[1];
                            this.emSections = response.data[1].data;
                        }
                    });
            } else {
                this.searchMode = false;
                this.loadEMSections();
            }
        }
    },
    mounted() {
        this.loadEMSections();
        this.$emit('loadBreadcrumbLink', {
            url: '/equipment-maintenance-sections',
            pageName: 'Tutorijal o održavanju opreme'
        });
        EventBus.$on('load-em-sections', () => this.loadEMSections());
    }
}

</script>

<style scoped>

</style>
