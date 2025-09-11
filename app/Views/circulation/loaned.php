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
						if((!isset($tab) || $tab == 'peminjaman-mhs' || $tab == '') ? 'active' : '') {
							$peminjaman_mhs_active = 'active';
							$peminjaman_mhs_show = 'show';
						} else {
							$peminjaman_mhs_active = '';
							$peminjaman_mhs_show = '';
						}
						
						if((isset($tab) && $tab == 'peminjaman-dosen' && $tab != '') ? 'active' : '') {
							$peminjaman_dosen_active = 'active';
							$peminjaman_dosen_show = 'show';
						} else {
							$peminjaman_dosen_active = '';
							$peminjaman_dosen_show = '';
						}
						?>
                        <div class="row">
                            <div class="col-md-12">
								<ul class="nav nav-pills nav-primary nav-pills-no-bd" id="pills-tab-without-border" role="tablist">
									<li class="nav-item" id="peminjaman-mhs">
										<a class="nav-link font-weight-bold active"id="pills-peminjaman-mhs-tab-nobd"
										data-toggle="pill" href="#pills-peminjaman-mhs-nobd" role="tab" aria-controls="pills-peminjaman-mhs-nobd" aria-selected="true">Mahasiswa</a>
									</li>
									<li class="nav-item" id="peminjaman-dosen">
										<a class="nav-link font-weight-bold" id="pills-peminjaman-dosen-tab-nobd" data-toggle="pill"
										href="#pills-peminjaman-dosen-nobd" role="tab" aria-controls="pills-peminjaman-dosen-nobd" aria-selected="false">Dosen</a>
									</li>
								</ul>
								<div class="tab-content mt-2 mb-3" id="pills-without-border-tabContent">
									<div class="tab-pane fade show active"
									id="pills-peminjaman-mhs-nobd" role="tabpanel" aria-labelledby="pills-peminjaman-mhs-tab-nobd">
										<div class="card">
											<div class="card-body">
												<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
												<div class="table-responsive">
													<table class="table table-striped table-hover" id="tabel-peminjaman-mhs" style="width:100%">
														<thead>
															<tr>
																<th><div class="pr-3">No</div></th>
																<th><div class="pr-3">NIM</div></th>
																<th><div class="pr-3">Nama</div></th>
																<th><div class="pr-3">Prodi</div></th>
																<th><div class="pr-3">Tgl Peminjaman</div></th>
																<th><div class="pr-3">Tenggat Waktu</div></th>
																<th><div class="pr-3">Rincian</div></th>
															</tr>
														</thead>
														<tbody></tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade"
									id="pills-peminjaman-dosen-nobd" role="tabpanel" aria-labelledby="pills-peminjaman-dosen-tab-nobd">
										<div class="card">
											<div class="card-body">
												<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
												<div class="table-responsive">
													<table class="table table-striped table-hover" id="tabel-peminjaman-dosen" style="width:100%">
														<thead>
															<tr>
																<th><div class="pr-3">No</div></th>
																<th><div class="pr-3">NIDN</div></th>
																<th><div class="pr-3">Nama</div></th>
																<th><div class="pr-3">Tgl Peminjaman</div></th>
																<th><div class="pr-3">Tenggat Waktu</div></th>
																<th><div class="pr-3">Rincian</div></th>
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
			
			
			var oTable = $("#tabel-peminjaman-mhs").dataTable({
				"search": {
					"search": "<?php echo $searchPeminjamanMhs; ?>"
				},
				"columnDefs": [
					{ "name": "", "targets": 0, "orderable": false },
					{ "name": "a.nim", "targets": 1 },
					{ "name": "b.name", "targets": 2 },
					{ "name": "b.major", "targets": 3 },
					{ "name": "a.tgl_pinjam", "targets": 4 },
					{ "name": "a.target_tgl_kembali", "targets": 5 },
					{ "name": "", "targets": 6, data: 7, "orderable": false }
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": false,
				"iDisplayStart": <?php echo ((isset($startPeminjamanMhs) && $startPeminjamanMhs != '') ? $startPeminjamanMhs : 0)?>,
				"iDisplayLength" : <?php echo ((isset($lengthPeminjamanMhs) && $lengthPeminjamanMhs != '') ? $lengthPeminjamanMhs : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/circulation/loanedMhsList",
					"type": "POST",
					"data": {
						'search': function(){return $("input[aria-controls=tabel-peminjaman-mhs]").val();},
						'status': 'loaned'
					}
				}
			});
			
			var oTable2 = $("#tabel-peminjaman-dosen").dataTable({
				"search": {
					"search": "<?php echo $searchPeminjamanDosen; ?>"
				},
				"columnDefs": [
					{ "name": "", "targets": 0, "orderable": false },
					{ "name": "a.nidn", "targets": 1 },
					{ "name": "b.nama_sdm", "targets": 2 },
					{ "name": "a.tgl_pinjam", "targets": 3 },
					{ "name": "a.target_tgl_kembali", "targets": 4 },
					{ "name": "", "targets": 5, data: 6, "orderable": false }
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": false,
				"iDisplayStart": <?php echo ((isset($startPeminjamanDosen) && $startPeminjamanDosen != '') ? $startPeminjamanDosen : 0)?>,
				"iDisplayLength" : <?php echo ((isset($lengthPeminjamanDosen) && $lengthPeminjamanDosen != '') ? $lengthPeminjamanDosen : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/circulation/loanedDosenList",
					"type": "POST",
					"data": {
						'search': function(){return $("input[aria-controls=tabel-peminjaman-dosen]").val();},
						'status': 'loaned'
					}
				}
			});
        </script>
    </body>
</html>