<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
	</head>
    <body data-background-color="bg3">
        <div id="wrapper">
			<?php echo view('partials/nav'); ?>
			
			<div class="main-panel animated fadeIn">
                <div class="content">
                    <div class="page-inner">
                        <div class="page-header">
                            <h1 class="page-title"><?php echo $title; ?></h1>
							<ul class="breadcrumbs">
								<li class="nav-home">
									<a href="/dashboard">
								<i class="flaticon-home"></i>
									</a>
								</li>
								<?php foreach($breadcrumbs->getResult() as $bc) { ?>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<a href="<?php echo $bc->href; ?>"><?php echo $bc->name; ?></a>
								</li>
								<?php } ?>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<?php echo $nama_lab; ?>
								</li>
								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<?php echo $title; ?>
								</li>
							</ul>
                        </div>
						<?php if (session()->has('message')) : ?>
						
						<div class="alert alert-success">
							<h2><?php echo session('message') ?></h2>
						</div>
						<?php endif ?>
						<?php if (session()->has('error')) : ?>
						
						<div class="alert alert-danger">
							<h2><?php echo session('error') ?></h2>
						</div>
						<?php endif ?>
						<?php
						if((!isset($tab) || $tab == 'inventaris' || $tab == '') ? 'active' : '') {
							$inventaris_active = 'active';
							$inventaris_show = 'show';
						} else {
							$inventaris_active = '';
							$inventaris_show = '';
						}
						
						if((isset($tab) && $tab == 'item' && $tab != '') ? 'active' : '') {
							$item_active = 'active';
							$item_show = 'show';
						} else {
							$item_active = '';
							$item_show = '';
						}
						
						if((isset($tab) && $tab == 'kotak-penyimpanan' && $tab != '') ? 'active' : '') {
							$kotak_penyimpanan_active = 'active';
							$kotak_penyimpanan_show = 'show';
						} else {
							$kotak_penyimpanan_active = '';
							$kotak_penyimpanan_show = '';
						}
						
						if((isset($tab) && $tab == 'daftar-kotak-penyimpanan' && $tab != '') ? 'active' : '') {
							$daftar_kotak_penyimpanan_active = 'active';
							$daftar_kotak_penyimpanan_show = 'show';
						} else {
							$daftar_kotak_penyimpanan_active = '';
							$daftar_kotak_penyimpanan_show = '';
						}
						?>
                        <div class="row">
                            <div class="col-md-12">
								<ul class="nav nav-pills nav-primary nav-pills-no-bd" id="pills-tab-without-border" role="tablist">
									<li class="nav-item ">
										<button id="back" class="nav-link font-weight-bold btn btn-danger text-white"><i class="fa fa-arrow-left mr-2"></i>Kembali</button>
									</li>
									<li class="nav-item" id="inventaris">
										<a class="nav-link font-weight-bold <?php echo $inventaris_active; ?>" id="pills-inventaris-tab-nobd"
										data-toggle="pill" href="#pills-inventaris-nobd" role="tab" aria-controls="pills-inventaris-nobd" aria-selected="true">Inventaris</a>
									</li>
									<li class="nav-item" id="item">
										<a class="nav-link font-weight-bold <?php echo $item_active; ?>" id="pills-item-tab-nobd"
										data-toggle="pill" href="#pills-item-nobd" role="tab" aria-controls="pills-item-nobd" aria-selected="false">Daftar Item</a>
									</li>
									<li class="nav-item" id="kotak-penyimpanan">
										<a class="nav-link font-weight-bold <?php echo $kotak_penyimpanan_active; ?>" id="pills-kotak-penyimpanan-tab-nobd" data-toggle="pill"
										href="#pills-kotak-penyimpanan-nobd" role="tab" aria-controls="pills-kotak-penyimpanan-nobd" aria-selected="false">Kotak Penyimpanan</a>
									</li>
									<li class="nav-item" id="daftar-kotak-penyimpanan">
										<a class="nav-link font-weight-bold <?php echo $daftar_kotak_penyimpanan_active; ?>" id="pills-daftar-kotak-penyimpanan-tab-nobd"
										data-toggle="pill" href="#pills-daftar-kotak-penyimpanan-nobd" role="tab" aria-controls="pills-daftar-kotak-penyimpanan-nobd"
										aria-selected="false">Daftar Kotak Penyimpanan</a>
									</li>
								</ul>
								<div class="tab-content mt-2 mb-3" id="pills-without-border-tabContent">
									<div class="tab-pane fade <?php echo $inventaris_show; ?> <?php echo $inventaris_active; ?>"
									id="pills-inventaris-nobd" role="tabpanel" aria-labelledby="pills-inventaris-tab-nobd">
										<div class="card">
											<div class="card-header">
												<?php if(has_permission('inventarisAdd')) { ?>
												<button id="inventarisAdd" class="btn btn-primary btn-sm font-weight-bold">
													<i class="fa fa-plus mr-2"></i>Tambah
												</button>
												<?php } ?>
											</div>
											<div class="card-body">
												<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
												<div class="table-responsive">
													<table class="table table-striped table-hover" id="tabelInventaris" style="width:100%">
														<thead>
															<tr>
																<th><div class="pr-3">No</div></th>
																<th><div class="pr-3">Nama</div></th>
																<th><div class="pr-3">Deskripsi</div></th>
																<th><div class="pr-3">Awalan Kode</div></th>
																<th><div class="pr-3">Akhiran Kode</div></th>
																<th><div class="pr-3">Panjang Kode</div></th>
																<th><div class="pr-3">Jumlah</div></th>
																<?php if(has_permission('inventarisEdit') || has_permission('inventarisDelete')) { ?>
																<th><div class="pr-3 text-right" style="width:60px">Aksi</div></th>
																<?php } ?>
															</tr>
														</thead>
														<tbody></tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade <?php echo $item_show; ?> <?php echo $item_active; ?>"
									id="pills-item-nobd" role="tabpanel" aria-labelledby="pills-item-tab-nobd">
										<div class="card">
											<div class="card-header">
												<?php if(has_permission('inventarisAdd')) { ?>
												<button id="itemAdd" class="btn btn-primary btn-sm font-weight-bold">
													<i class="fa fa-plus mr-2"></i>Tambah
												</button>
												<?php } if(has_permission('inventarisDelete')) {?>
												<button id="itemsDelete" class="btn btn-danger btn-sm font-weight-bold">
													<i class="fa fa-trash mr-2"></i>Hapus Terpilih
												</button>
												<?php } if(has_permission('inventarisDelete')) { ?>
												<button id="itemBarcodeGenerate" class="btn btn-dark btn-sm font-weight-bold">
													<i class="fa fa-print mr-2"></i>Cetak Barkode Terpilih
												</button>
												<?php } ?>
											</div>
											<div class="card-body">
												<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
												<div class="table-responsive">
													<table class="table table-striped table-hover" id="tabelItem" style="width:100%">
														<thead>
															<tr>
																<th style="width:1px"><div class="pr-3"><input type="checkbox" id="check-all"></div></th>
																<th><div class="pr-3">Kode Item</div></th>
																<th><div class="pr-3">Nama</div></th>
																<th><div class="pr-3">Kotak Penyimpanan</div></th>
																<th><div class="pr-3">Keterangan</div></th>
																<?php if(has_permission('inventarisEdit') || has_permission('inventarisDelete')) { ?>
																<th><div class="pr-3 text-right">Aksi</div></th>
																<?php } ?>
															</tr>
														</thead>
														<tbody></tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade <?php echo $kotak_penyimpanan_show; ?> <?php echo $kotak_penyimpanan_active; ?>"
									id="pills-kotak-penyimpanan-nobd" role="tabpanel" aria-labelledby="pills-kotak-penyimpanan-tab-nobd">
										<div class="card">
											<div class="card-header">
												<?php if(has_permission('inventarisAdd')) { ?>
												<button id="kotakPenyimpananAdd" class="btn btn-primary btn-sm font-weight-bold">
													<i class="fa fa-plus mr-2"></i>Tambah
												</button>
												<?php } ?>
											</div>
											<div class="card-body">
												<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
												<div class="table-responsive">
													<table class="table table-striped table-hover" id="tabelKotakPenyimpanan" style="width:100%">
														<thead>
															<tr>
																<th><div class="pr-3">No</div></th>
																<th><div class="pr-3">Nama</div></th>
																<th><div class="pr-3">Deskripsi</div></th>
																<th><div class="pr-3">Awalan Kode</div></th>
																<th><div class="pr-3">Akhiran Kode</div></th>
																<th><div class="pr-3">Panjang Kode</div></th>
																<th><div class="pr-3">Jumlah</div></th>
																<?php if(has_permission('inventarisEdit') || has_permission('inventarisDelete')) { ?>
																<th><div class="pr-3 text-right" style="width:60px">Aksi</div></th>
																<?php } ?>
															</tr>
														</thead>
														<tbody></tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade <?php echo $daftar_kotak_penyimpanan_show; ?> <?php echo $daftar_kotak_penyimpanan_active; ?>"
									id="pills-daftar-kotak-penyimpanan-nobd" role="tabpanel" aria-labelledby="pills-daftar-kotak-penyimpanan-tab-nobd">
										<div class="card">
											<div class="card-header">
												<?php if(has_permission('inventarisAdd')) { ?>
												<button id="daftarKotakPenyimpananAdd" class="btn btn-primary btn-sm font-weight-bold">
													<i class="fa fa-plus mr-2"></i>Tambah
												</button>
												<?php } if(has_permission('inventarisDelete')) {?>
												<button id="daftarKotakPenyimpanansDelete" class="btn btn-danger btn-sm font-weight-bold">
													<i class="fa fa-trash mr-2"></i>Hapus Terpilih
												</button>
												<?php } if(has_permission('inventarisDelete')) { ?>
												<button id="daftarKotakPenyimpananBarcodeGenerate" class="btn btn-dark btn-sm font-weight-bold">
													<i class="fa fa-print mr-2"></i>Cetak Barkode Terpilih
												</button>
												<?php } ?>
											</div>
											<div class="card-body">
												<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
												<div class="table-responsive">
													<table class="table table-striped table-hover" id="tabelDaftarKotakPenyimpanan" style="width:100%">
														<thead>
															<tr>
																<th style="width:1px"><div class="pr-3"><input type="checkbox" id="check-all-kotak"></div></th>
																<th><div class="pr-3">Kode Kotak</div></th>
																<th><div class="pr-3">Nama</div></th>
																<th><div class="pr-3">Keterangan</div></th>
																<?php if(has_permission('inventarisEdit') || has_permission('inventarisDelete')) { ?>
																<th><div class="pr-3 text-right">Aksi</div></th>
																<?php } ?>
															</tr>
														</thead>
														<tbody></tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
                            </div>
                            <!-- /.panel -->
                        </div>
                        <!-- /.col-lg-12 -->
                    </div>
                </div>
				<?php echo view('partials/footer'); ?>
				
            </div>
        </div>
		
		<script src="/assets/js/core/jquery.3.2.1.min.js"></script>
		<script src="/assets/js/core/popper.min.js"></script>
		<script src="/assets/js/core/bootstrap.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
		<script src="/assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
		<script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
		<script src="/assets/js/ready.min.js"></script>
		
		<script src="/assets/js/plugin/datatables/datatables.min.js"></script>
		
        <script>
			
			$.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
				if(oSettings.oFeatures.bServerSide === false){
					var before = oSettings._iDisplayStart;
					oSettings.oApi._fnReDraw(oSettings);
					oSettings._iDisplayStart = before;
					oSettings.oApi._fnCalculateEnd(oSettings);
				}
				oSettings.oApi._fnDraw(oSettings);
			};
			
			$("#check-all").change(function(){
				$(".kode-item").prop("checked", $(this).prop("checked"));
				$(".nama-item").prop("checked", $(this).prop("checked"));
			});
			
			$("#check-all-kotak").change(function(){
				$(".kode-kotak").prop("checked", $(this).prop("checked"));
				$(".nama-kotak").prop("checked", $(this).prop("checked"));
			});
			
			$('#back').click(function(){
				parent.history.back();
				return false;
			});
			
			$('#inventarisAdd').click(function(){
				window.location.assign('/data/inventarisAdd/<?php echo $id_lab; ?>');
			});
			
			$('#itemAdd').click(function(){
				window.location.assign('/data/itemAdd/<?php echo $id_lab; ?>');
			});
			
			$('#kotakPenyimpananAdd').click(function(){
				window.location.assign('/data/kotakPenyimpananAdd/<?php echo $id_lab; ?>');
			});
			
			$('#daftarKotakPenyimpananAdd').click(function(){
				window.location.assign('/data/daftarKotakPenyimpananAdd/<?php echo $id_lab; ?>');
			});
			
			$('#itemBarcodeGenerate').click(function(){
				$.ajax({
					url: "/data/generateItemBarcode",
					type: "POST",
					data: $('input:checkbox:checked').serialize() + '&id_lab=' + <?php echo $id_lab; ?>,
					success: function(data) {
						//$('#response').html(data);
						window.location.assign('/data/generateItemBarcode');
					}
				});
			});
			
			$('#daftarKotakPenyimpananBarcodeGenerate').click(function(){
				$.ajax({
					url: "/data/generateKotakBarcode",
					type: "POST",
					data: $('input:checkbox:checked').serialize() + '&id_lab=' + <?php echo $id_lab; ?>,
					success: function(data) {
						//$('#response').html(data);
						window.location.assign('/data/generateKotakBarcode');
					}
				});
			});
			
			$('#itemsDelete').click(function(){
				swal({
					title: "Apakah anda yakin?",
					text: "Semua data akan dihapus dan tidak dapat dikembalikan!",
					icon: "warning",
					buttons:{
						cancel: {
							visible: true,
							text : "Batal",
							className: "btn"
						},
						confirm: {
							text : "Hapus",
							className : "btn btn-danger"
						}
					}
				})
				.then((willDeletes) => {
					if (willDeletes) {
						$.ajax({
							url: "/data/itemsDelete",
							type: "POST",
							data: $('input:checkbox:checked').serialize() + '&id_lab=' + <?php echo $id_lab; ?>,
							success: function(data) {
								//$('#response').html(data);
								oTable2.fnStandingRedraw();
							}
						});
					}
				});
			});
			
			$('#daftarKotakPenyimpanansDelete').click(function(){
				swal({
					title: "Apakah anda yakin?",
					text: "Semua data akan dihapus dan tidak dapat dikembalikan!",
					icon: "warning",
					buttons:{
						cancel: {
							visible: true,
							text : "Batal",
							className: "btn"
						},
						confirm: {
							text : "Hapus",
							className : "btn btn-danger"
						}
					}
				})
				.then((willDeletes) => {
					if (willDeletes) {
						$.ajax({
							url: "/data/daftarKotakPenyimpanansDelete",
							type: "POST",
							data: $('input:checkbox:checked').serialize() + '&id_lab=' + <?php echo $id_lab; ?>,
							success: function(data) {
								//$('#response').html(data);
								oTable4.fnStandingRedraw();
							}
						});
					}
				});
			});
			
			$('#pills-inventaris-tab-nobd').click(function(){
				$.ajax({
					type: "POST",
					url: '/data/inventarisTab',
					"data": {
						'tab' : 'inventaris',
					}
				});
			});
			
			$('#pills-item-tab-nobd').click(function(){
				$.ajax({
					type: "POST",
					url: '/data/inventarisTab',
					"data": {
						'tab' : 'item',
					}
				});
			});
			
			$('#pills-kotak-penyimpanan-tab-nobd').click(function(){
				$.ajax({
					type: "POST",
					url: '/data/inventarisTab',
					"data": {
						'tab' : 'kotak-penyimpanan',
					}
				});
			});
			
			$('#pills-daftar-kotak-penyimpanan-tab-nobd').click(function(){
				$.ajax({
					type: "POST",
					url: '/data/inventarisTab',
					"data": {
						'tab' : 'daftar-kotak-penyimpanan',
					}
				});
			});
			
			var oTable = $("#tabelInventaris").dataTable({
				"search": {
					"search": "<?php echo $searchInventaris; ?>"
				},
				"columnDefs": [
					{ "name": "", "targets": 0, "orderable": false },
					{ "name": "a.nama", "targets": 1 },
					{ "name": "a.deskripsi", "targets": 2 },
					{ "name": "a.code_prefix", "targets": 3 },
					{ "name": "a.code_suffix", "targets": 4 },
					{ "name": "a.code_length", "targets": 5 },
					{ "name": "jml_item", "targets": 6 }
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": true,
				"order": [
					[
						<?php echo ((isset($orderColInventaris) && $orderColInventaris != '') ? $orderColInventaris : 1)?>,
						"<?php echo ((isset($orderDirInventaris) && $orderDirInventaris != '') ? $orderDirInventaris : 'asc')?>"
					]
				],
				"iDisplayStart": <?php echo ((isset($startInventaris) && $startInventaris != '') ? $startInventaris : 0)?>,
				"iDisplayLength" : <?php echo ((isset($lengthInventaris) && $lengthInventaris != '') ? $lengthInventaris : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/data/inventarisList",
					"type": "POST",
					"data": {
						'id_lab' : "<?php echo $id_lab; ?>",
						'search': function(){return $("input[aria-controls=tabelInventaris]").val();}
					}
				}
			});
			
			var oTable2 = $("#tabelItem").dataTable({
				/*"aLengthMenu": [
					[10, 25, 50, 100, 250, 500, 750, 1500],
					[10, 25, 50, 100, 250, 500, 750, 1500]
				],*/
	
				"search": {
					"search": "<?php echo $searchItem; ?>"
				},
				"columnDefs": [
					{ "name": "", "targets": 0, "orderable": false },
					{ "name": "a.kode", "targets": 1 },
					{ "name": "b.nama", "targets": 2 },
					{ "name": "c.kode", "targets": 3 },
					{ "name": "", "targets": 5, "orderable": false }
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": true,
				"order": [
					[
						<?php echo ((isset($orderColItem) && $orderColItem != '') ? $orderColItem : 1)?>,
						"<?php echo ((isset($orderDirItem) && $orderDirItem != '') ? $orderDirItem : 'asc')?>"
					]
				],
				"iDisplayStart": <?php echo ((isset($startItem) && $startItem != '') ? $startItem : 0)?>,
				"iDisplayLength" : <?php echo ((isset($lengthItem) && $lengthItem != '') ? $lengthItem : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/data/itemList",
					"type": "POST",
					"data": {
						'id_lab' : "<?php echo $id_lab; ?>",
						'search': function(){return $("input[aria-controls=tabelItem]").val();}
					}
				}
			});
			
			var oTable3 = $("#tabelKotakPenyimpanan").dataTable({
				"search": {
					"search": "<?php echo $searchKotakPenyimpanan; ?>"
				},
				"columnDefs": [
					{ "name": "", "targets": 0, "orderable": false },
					{ "name": "a.nama", "targets": 1 },
					{ "name": "a.deskripsi", "targets": 2 },
					{ "name": "a.code_prefix", "targets": 3 },
					{ "name": "a.code_suffix", "targets": 4 },
					{ "name": "a.code_length", "targets": 5 },
					{ "name": "jml_item", "targets": 6 }
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": true,
				"order": [
					[
						<?php echo ((isset($orderColKotakPenyimpanan) && $orderColKotakPenyimpanan != '') ? $orderColKotakPenyimpanan : 1)?>,
						"<?php echo ((isset($orderDirKotakPenyimpanan) && $orderDirKotakPenyimpanan != '') ? $orderDirKotakPenyimpanan : 'asc')?>"
					]
				],
				"iDisplayStart": <?php echo ((isset($startKotakPenyimpanan) && $startKotakPenyimpanan != '') ? $startKotakPenyimpanan : 0)?>,
				"iDisplayLength" : <?php echo ((isset($lengthKotakPenyimpanan) && $lengthKotakPenyimpanan != '') ? $lengthKotakPenyimpanan : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/data/kotakPenyimpananList",
					"type": "POST",
					"data": {
						'id_lab' : "<?php echo $id_lab; ?>",
						'search': function(){return $("input[aria-controls=tabelKotakPenyimpanan]").val();}
					}
				}
			});
			
			var oTable4 = $("#tabelDaftarKotakPenyimpanan").dataTable({
				"search": {
					"search": "<?php echo $searchDaftarKotakPenyimpanan; ?>"
				},
				"columnDefs": [
					{ "name": "", "targets": 0, "orderable": false },
					{ "name": "a.kode", "targets": 1 },
					{ "name": "b.nama", "targets": 2 },
					{ "name": "", "targets": 4, "orderable": false }
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": true,
				"order": [
					[
						<?php echo ((isset($orderColDaftarKotakPenyimpanan) && $orderColDaftarKotakPenyimpanan != '') ? $orderColDaftarKotakPenyimpanan : 1)?>,
						"<?php echo ((isset($orderDirDaftarKotakPenyimpanan) && $orderDirDaftarKotakPenyimpanan != '') ? $orderDirDaftarKotakPenyimpanan : 'asc')?>"
					]
				],
				"iDisplayStart": <?php echo ((isset($startDaftarKotakPenyimpanan) && $startDaftarKotakPenyimpanan != '') ? $startDaftarKotakPenyimpanan : 0)?>,
				"iDisplayLength" : <?php echo ((isset($lengthDaftarKotakPenyimpanan) && $lengthDaftarKotakPenyimpanan != '') ? $lengthDaftarKotakPenyimpanan : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/data/daftarKotakPenyimpananList",
					"type": "POST",
					"data": {
						'id_lab' : "<?php echo $id_lab; ?>",
						'search': function(){return $("input[aria-controls=tabelDaftarKotakPenyimpanan]").val();}
					}
				}
			});
        </script>
    </body>
</html>