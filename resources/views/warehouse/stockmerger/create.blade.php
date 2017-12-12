@extends('layouts.adminlte.master')

@section('title')
    @lang('warehouse.stockmerger.create.title')
@endsection

@section('page_title')
    <span class="fa fa-sort-amount-asc fa-fw"></span>&nbsp;@lang('warehouse.stockmerger.create.page_title')
@endsection

@section('page_title_desc')
    @lang('warehouse.stockmerger.create.page_title_desc')
@endsection

@section('breadcrumbs')
    {!! Breadcrumbs::render('stockmerger_create') !!}
@endsection

@section('content')
    <div id="smVue">
        <div v-show="errors.count() > 0" v-cloak>
            <div class="alert alert-danger">
                <strong>@lang('labels.GENERAL_ERROR_TITLE')</strong> @lang('labels.GENERAL_ERROR_DESC')<br><br>
                <ul v-for="(e, eIdx) in errors.all()">
                    <li>@{{ e }}</li>
                </ul>
            </div>
        </div>

        <form id="smForm" class="form-horizontal" method="post" @submit.prevent="validateBeforeSubmit()">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">@lang('warehouse.stockmerger.create.header.title.merger')</h3>
                        </div>
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputMergerDate" class="col-md-2">
                                    @lang('warehouse.stockmerger.field.merger_date')
                                </label>
                                <div class="col-md-10">
                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <vue-datetimepicker id="inputMergerDate" name="merge_date" v-model="sm.merge_date" format="YYYY-MM-DD hh:mm A"></vue-datetimepicker>
                                    </div>
                                </div>
                            </div>
                            <div v-bind:class="{ 'form-group':true, 'has-error':errors.has('merge_type') }">
                                <label for="inputMergerType" class="col-md-2">
                                    @lang('warehouse.stockmerger.field.merger_type')
                                </label>
                                <div class="col-md-10">
                                    <select class="form-control"
                                            name="merge_type"
                                            v-model="sm.merge_type"
                                            v-validate="'required'"
                                            data-vv-as="{{ trans('warehouse.stockmerger.field.merge_type') }}">
                                        <option v-bind:value="defaultMergeType">@lang('labels.PLEASE_SELECT')</option>
                                        <option v-for="(value, key) in mergeTypeDDL" v-bind:value="key">@{{ value }}</option>
                                    </select>
                                    <span v-show="errors.has('merge_type')" class="help-block" v-cloak>@{{ errors.first('merge_type') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">@lang('warehouse.stockmerger.create.header.title.stock_lists')</h3>
                        </div>
                        <div class="box-body">
                            <div v-bind:class="{ 'form-group':true, 'has-error':errors.has('stockLists') }">
                                <label for="inputStockLists" class="col-md-2">@lang('warehouse.stockmerger.field.stock_lists')</label>
                                <div class="col-md-10">
                                    <select class="form-control"
                                            name="stockLists"
                                            v-model="selected_prod_id"
                                            v-validate="'required'"
                                            v-on:change="retrieveStockByProductId"
                                            data-vv-as="{{ trans('warehouse.stockmerger.field.stock_lists') }}">
                                        <option v-bind:value="defaultStockLists">@lang('labels.PLEASE_SELECT')</option>
                                        <option v-for="s in stocksDDL" v-bind:value="s.product_id">@{{ s.name }}</option>
                                    </select>
                                    <span v-show="errors.has('stockLists')" class="help-block" v-cloak>@{{ errors.first('stockLists') }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="stockTable" class="col-md-2"></label>
                                <div class="col-md-10">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>PO Date</th>
                                                <th>Stock</th>
                                                <th>Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(s, sIdx) in stockLists" v-cloak>
                                                <td>1</td>
                                                <td>@{{ s }}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr v-show="stockLoading">
                                                <td colspan="4"><i class="fa fa-spinner fa-spin"></i></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    @{{ this.stockLists }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">@lang('warehouse.stockmerger.create.header.title.merger_remarks')</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="inputRemarks">
                                        @lang('warehouse.stockmerger.field.remarks')
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <textarea id="inputRemarks" name="remarks" class="form-control" rows="5" v-model="sm.remarks"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('custom_js')
    <script type="application/javascript">
        var smApp = new Vue({
            el: '#smVue',
            data: {
                sm: {
                    merge_date:'',
                    merge_type: '',
                    remarks: '',
                    details: { }
                },
                selected_prod_id: '',
                stockLists: [],
                stockLoading: false,
                stocksDDL: JSON.parse('{!! $stocks !!}'),
                mergeTypeDDL: JSON.parse('{!! $stockMergeDDL !!}'),
            },
            methods: {
                validateBeforeSubmit: function() {
                    var vm = this;
                    this.$validator.validateAll().then(function(isValid) {
                        if (!isValid) return;
                        $('#loader-container').fadeIn('fast');
                        axios.post('{{ route('api.post.db.warehouse.transfer_stock.transfer') }}' + '?api_token=' + $('#secapi').val(), new FormData($('#tsForm')[0]))
                            .then(function(response) {
                                window.location.href = '{{ route('db.warehouse.transfer_stock.transfer') }}';
                            }).catch(function(e) {
                            $('#loader-container').fadeOut('fast');
                            if (e.response.data.errors != undefined && Object.keys(e.response.data.errors).length > 0) {
                                for (var key in e.response.data.errors) {
                                    for (var i = 0; i < e.response.data.errors[key].length; i++) {
                                        vm.$validator.errors.add('', e.response.data.errors[key][i], 'server', '__global__');
                                    }
                                }
                            } else {
                                vm.$validator.errors.add('', e.response.status + ' ' + e.response.statusText, 'server', '__global__');
                                if (e.response.data.message != undefined) { console.log(e.response.data.message); }
                            }
                        });
                    });
                },
                retrieveStockByProductId: function() {
                    if (this.selected_prod_id == '') return;
                    this.stockLoading = true;

                    axios.get('{{ route('api.get.stock.byproduct') }}' + '?api_token=' + $('#secapi').val(), {
                        params: {
                            pId: this.selected_prod_id
                        }
                    }).then(function (response) {
                        this.stockLists = response.data;
                        this.stockLoading = false;
                    });
                },
            },
            mounted: function() {


            },
            computed: {
                defaultStockLists: function() {
                    return ''
                },
                defaultMergeType: function() {
                    return ''
                }
            }
        });
    </script>
@endsection