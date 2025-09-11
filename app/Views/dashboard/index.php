<!DOCTYPE html>
<html lang="en">
	<head>
		<?php echo view('partials/head'); ?>
		
		<link href="/assets/fullcalendar/fullcalendar.css" rel="stylesheet" />
		<link href="/assets/fullcalendar/fullcalendar.print.css" rel="stylesheet" media="print" />
		<script src="/assets/js/core/jquery.3.2.1.min.js"></script>
		<script src="/assets/fullcalendar/fullcalendar.min.js"></script>
		<style>
		.fc-day-number{font-size:15px;font-weight:bold}
		.fc-event-title{font-size:15px;font-weight:bold}
		</style>
		<script>
		$(document).ready(function() {
			var date = new Date();
			var d = date.getDate();
			var m = date.getMonth();
			var y = date.getFullYear();
			
			var calendar = $('#calendar').fullCalendar({
				contentHeight: 300,
				selectable: true,
				select: function(date, jsEvent, view) {
					var Y = date.getFullYear();
					var m = ('0' + (date.getMonth() + 1)).slice(-2);
					var d = ('0' + date.getDate()).slice(-2);
					window.location.assign('/attendance/date/' + Y + "-" + m + "-"+ d)
				},
				eventSources: [
					{
						url: '/attendanceCalendar',
						method: 'POST',
						extraParams: {
							custom_param1: 'something',
							custom_param2: 'somethingelse'
						},
						failure: function() {
							alert('there was an error while fetching events!');
						},
						color: 'transparent',
						textColor: 'green'
					}
				]
			});
		});
		</script>
	</head>
    <body data-background-color="bg3">
        <div class="wrapper">
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

								<?php foreach($breadcrumbs->getResult() as $bc): ?>

								<li class="separator">
									<i class="flaticon-right-arrow"></i>
								</li>
								<li class="nav-item">
									<a href="<?php echo $bc->href; ?>"><?php echo $bc->name; ?></a>
								</li>

								<?php endforeach; ?>
							</ul>
                        </div>
						
						<?php if (session()->has('message')) : ?>
						
						<div class="alert alert-success">
							<h2><?php echo session('message') ?></h2>
						</div>
						
						<?php endif ?>

						<?php echo view('dashboard/peminjaman'); ?>

					</div>
				</div>

				<?php echo view('partials/footer'); ?>
				
			</div>
		</div>
		<script src="/assets/js/core/popper.min.js"></script>
		<script src="/assets/js/core/bootstrap.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
		<script src="/assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
		<script src="/assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
		<script src="/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
		<script src="/assets/js/ready.min.js"></script>
		
		<script src="/assets/js/plugin/datatables/datatables.min.js"></script>
		
		<script>
			var oTable = $("#courses").dataTable({
				"columnDefs": [
					{ "name": "a.name", "targets": 1 },
					{ "name": "a.start", "targets": 2 },
					{ "name": "a.end", "targets": 3 }
				],
				"pagingType": "numbers",
				"searching": false,
				"ordering": true,
				"order": [[<?php echo ((isset($orderCol) && $orderCol != '') ? $orderCol : 0)?>, "<?php echo ((isset($orderDir) && $orderDir != '') ? $orderDir : 'asc')?>"]],
				"iDisplayStart": <?php echo ((isset($start) && $start != '') ? $start : 0)?>,
				"iDisplayLength" : <?php echo ((isset($length) && $length != '') ? $length : 10)?>,
				"bFilter" : false,							 
				"bLengthChange": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "/courseList",
					"type": "POST"
				}
			});
        </script>
    </body>
</html>