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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
									<div class="card-header">
										<?php if(has_permission('masterDataStudentsSync')) { ?>
										<button id="add" class="btn btn-primary btn-sm font-weight-bold"><i class="fa fa-sync mr-2"></i>Sinkronisasi</button>
										<?php } ?>
									</div>
                                    <div class="card-body">
										<script src="/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
										<div class="table-responsive">
											<table class="table table-striped table-hover" id="table1" style="width:100%">
												<thead>
													<tr>
														<th><div class="pr-3">No</div></th>
														<th><div class="pr-3">NIDN</div></th>
														<th><div class="pr-3">Nama</div></th>
														<th><div class="pr-3">Jenis</div></th>
													</tr>
												</thead>
												<tbody></tbody>
											</table>
										</div>
                                    </div>
                                </div>
                                <!-- /.panel-body -->
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
			$('#add').click(function(){
				var id = $(this).attr("id");
				var title = $(this).attr("title");
				swal({
					title: "Sinkronisasi data dosen?",
					icon: "warning",
					buttons:{
						cancel: {
							visible: true,
							text : "Batal",
							className: "btn"
						},
						confirm: {
							text : "Sinkronisasi",
							className : "btn btn-primary"
						}
					}
				})
				.then((willSync) => {
					if (willSync) {
						window.location.assign('/masterdata/dosenSync')
					}
				});
			});
			
            var oTable = $("#table1").dataTable({
				"search": {
					"search": "<?php echo $search; ?>"
				},
				"columnDefs": [
					{ "targets": 0, "className": 'select-checkbox' },
					{ "name": "a.nidn", "targets": 1 },
					{ "name": "a.nama_sdm", "targets": 2},
					{ "name": "a.jenis_sdm", "targets": 3},
				],
				"pagingType": "numbers",
				"searching": true,
				"ordering": true,
				"order": [[<?php echo ((isset($orderCol) && $orderCol != '') ? $orderCol : 2)?>, "<?php echo ((isset($orderDir) && $orderDir != '') ? $orderDir : 'asc')?>"]],
				"iDisplayStart": <?php echo ((isset($start) && $start != '') ? $start : 0)?>,
				"iDisplayLength" : <?php echo ((isset($length) && $length != '') ? $length : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/masterdata/dosenList",
					"type": "POST",
					"data": {
						'search': function(){return $("input[type=search]").val();}
					}
				}
			});
        </script>
    </body>
</html>