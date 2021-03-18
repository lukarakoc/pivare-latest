<template>
    <div class="modal fade" id="permissions-modal" tabindex="-1" role="dialog"
         aria-labelledby="permissions-modal-label" aria-hidden="true" ref="permissionsModalRef">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissions-modal-label">Permisije</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form @submit="savePermissions">
                    <div class="modal-body">
                        <div v-if="pageIsLoading" class="text-center">
                            <spinner/>
                        </div>
                        <div v-for="(permission, index) in permissions" :key="index" v-if="!pageIsLoading">
                            <div class="form-group mx-2 mt-2">
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox"
                                           :id="index"
                                           v-model="permissionsForm[index]">
                                    <label :for="index">
                                        {{ permission.name }}
                                    </label>
                                </div>
                            </div>
                            <hr>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Odustani</button>
                        <button id="updateUser"
                                class="btn btn-primary"
                                type="submit"
                                @click="storeUpdateDisabled = true">
                            Sačuvaj
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
import {EventBus, swalError, swalSuccess} from "../../../main";
import Spinner from "../../Spinner";

export default {
    name: "PermissionsModal",
    components: {
        Spinner
    },
    data() {
        return {
            pageIsLoading: true,
            storeUpdateDisabled: false,
            permissions: [],
            permissionsForm: [],
            userId: ''
        }
    },
    methods: {
        async openModal(data) {
            this.fillForm(data);
            $('#permissions-modal').modal();

        },
        fillForm(data) {
            axios.get(`/admin/user-permissions/${data}`)
                .then(response => {
                    if (response.data[0] === "success") {
                        for (let i = 0; i < this.permissions.length; i++) {
                            this.permissionsForm[i] = response.data[1][i] != null && response.data[1][i].permission_value === 1;
                        }
                        this.userId = data;
                        this.pageIsLoading = false;
                    }
                });
        },
        loadPermissions() {
            axios.get(`/admin/user-permissions`)
                .then(response => {
                    if (response.data[0] === "success") {
                        this.permissions = response.data[1];
                    }
                });
        },
        savePermissions() {
            let i, j;
            var data = new FormData;
            for (i = 0; i < this.permissionsForm.length; i++) {
                data.append(`permissionsData[${i}]`, this.permissionsForm[i]);
            }
            for (j = 0; j < this.permissions.length; j++) {
                data.append('permissions[]', this.permissions[j].id);
            }
            data.append('user', this.userId);

            const config = {headers: {'content-type': 'multipart/form-data'}};
            axios.post(`/admin/user-permissions/${this.userId}/save`, data, config)
                .then((response) => {
                    this.storeUpdateDisabled = false;
                    if (response.data[0] === "success") {
                        $("#permissions-modal").modal("hide");
                        EventBus.$emit('load-users');
                        swalSuccess("Permisije su uspješno sačuvane.");
                    }
                }).catch(error => {
                this.storeUpdateDisabled = false;
                if (error.response.status === 423) {
                    swalError(error.response.data)
                }
                console.log(error.response.data);
                console.log(error.response.status);
                console.log(error.response.headers);
            });
        },
        clearData() {
            this.permissionsForm = [];
            this.pageIsLoading = true;
            this.userId = '';
        }
    },
    mounted() {
        this.loadPermissions();
        EventBus.$on('open-permissions-modal', (data) => this.openModal(data));
        $(this.$refs.permissionsModalRef).on("hidden.bs.modal", this.clearData);

    }
}
</script>

<style scoped>

</style>
