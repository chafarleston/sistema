<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ title }}</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i
                    class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">Listado de {{ title }}</h3>
            </div>
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th>#</th>
                        <th class="text-left">Nro. de Placa</th>
                        <th class="text-left">Modelo</th>
                        <th class="text-left">Marca</th>
                        <th class="text-left">Certificado de habilitación vehicular</th>
                        <th class="text-center">Predeterminado</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td class="text-left">{{ row.plate_number }}</td>
                        <td class="text-left">{{ row.model }}</td>
                        <td class="text-left">{{ row.brand }}</td>
                        <td class="text-left">{{ row.tuc }}</td>
                        <td class="text-center">{{ row.is_default }}</td>
                        <td class="text-center">
                            <el-switch v-model="row.is_active" @change="toggleActiveTransport(row)" />
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"
                                    @click.prevent="clickCreate(row.id)">Editar
                            </button>
                            <template v-if="typeUser === 'admin'">
                                <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"
                                        @click.prevent="clickDelete(row.id)">Eliminar
                                </button>
                            </template>
                        </td>
                    </tr>
                </data-table>
            </div>

            <transport-form :showDialog.sync="showDialog"
                            :recordId="recordId"
                            @success="successCreate"></transport-form>
        </div>
    </div>
</template>

<script>

import TransportForm from './form'
import DataTable from '@components/DataTable.vue'
import {deletable} from '@mixins/deletable'

export default {
    name: 'DispatchTransportIndex',
    mixins: [deletable],
    props: ['typeUser'],
    components: {TransportForm, DataTable},
    data() {
        return {
            title: null,
            showDialog: false,
            resource: 'transports',
            recordId: null,
        }
    },
    created() {
        this.title = 'Vehículos'
    },
    methods: {
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            )
        },
        successCreate() {
            this.$eventHub.$emit('reloadData')
        },
        toggleActiveTransport(row) {
            this.$http
                .post(`/transports/${row.id}/toggle`, { is_active: row.is_active })
                .then(response => {
                    if (response.data.success) {
                        this.$eventHub.$emit('reloadData');
                    } else {
                        row.is_active = !row.is_active;
                        console.error('Error al actualizar el estado');
                    }
                })
                .catch(error => {
                    row.is_active = !row.is_active;
                    this.axiosError(error);
                });
        }
    }
}
</script>
