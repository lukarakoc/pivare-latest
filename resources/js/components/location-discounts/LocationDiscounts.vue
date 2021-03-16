<template>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Popusti</h3>
            </div>

            <div class="card-body pt-2">
                <button class="btn btn-sm btn-primary float-right mb-2"
                        @click="openCreateModal"
                        v-if="!hasDiscount">
                    Dodaj popust
                    <i class="fas fa-plus-circle"></i>
                </button>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center" width="10">ID</th>
                            <th class="text-center">Naziv</th>
                            <th class="text-center">Opis</th>
                            <th class="text-center">Datum od</th>
                            <th class="text-center">Datum do</th>
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
                        <tr v-for="discount in discounts" :key="discount.id">
                            <td class="text-center">{{ discount.id }}</td>
                            <td class="text-center">{{ discount.name.me }}</td>
                            <td class="text-center"
                                v-html="discount.description.me.substring(3, discount.description.me.length - 4)"></td>
                            <td class="text-center">{{ discount.date_from }}</td>
                            <td class="text-center">{{ discount.date_to }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" @click="openEditModal(discount)">
                                    <i class="fas fa-edit"></i>
                                    Izmijeni
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" @click="deleteDiscount(discount.id)">
                                    <i class="fas fa-trash"></i>
                                    Izbriši
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <create-and-edit-discount :location="location"></create-and-edit-discount>
    </div>
</template>

<script>
import Spinner from "../Spinner";
import {EventBus, swalError, swalSuccess} from "../../main";
import CreateAndEditDiscount from "./CreateAndEditDiscount";

export default {
    name: "LocationDiscounts",
    components: {
        Spinner,
        CreateAndEditDiscount
    },
    data() {
        return {
            pageIsLoading: true,
            searchMode: false,
            searchKeyword: '',
            discounts: [],
            hasDiscount: false,
            discountsPagination: {},
            location: this.$route.params.id
        }
    },
    methods: {
        loadDiscounts(page = 1) {
            axios.get(`/admin/locations-discounts/${this.$route.params.id}`)
                .then(response => {
                    if (response.data[0] === 'success') {
                        this.discounts = response.data[1];
                        if (this.discounts.length > 0) {
                            this.hasDiscount = true
                        } else {
                            this.hasDiscount = false;
                        }
                        this.pageIsLoading = false;
                    }
                });
        },
        openCreateModal(data) {
            EventBus.$emit('open-create-modal', data);
        },
        openEditModal(step) {
            EventBus.$emit('open-edit-modal', step);
        },
        deleteDiscount(id) {
            Swal.fire({
                icon: "warning",
                title: "Pažnja",
                text: "Da li ste sigurni da želite da izbrišete popust?",
                showCancelButton: true,
                confirmButtonColor: "#38c172",
                cancelButtonColor: "#c51f1a",
                confirmButtonText: "Da",
                cancelButtonText: "Ne"
            })
                .then((result) => {
                    if (result.value) {
                        axios.delete(`/admin/locations-discounts/${id}`)
                            .then(response => {
                                if (response.data[0] === 'success') {
                                    swalSuccess("Uspješno ste izbrisali popust!");
                                    EventBus.$emit('load-discounts');
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

    },
    mounted() {
        this.loadDiscounts();
        this.$emit('loadBreadcrumbLink', {url: '/locations/:id/discounts', pageName: 'Lokacija - popusti'});
        EventBus.$on('load-discounts', () => this.loadDiscounts());
    }
}

</script>

<style scoped>

</style>
