<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo APPLICATION_NAME .' - '. TTi18n::getText('Down For Maintenance');?></title>
    <base href="<?php echo $BASE_URL; ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="Keywords" content="workforce management, time and attendance, payroll software, online timesheet software, open source payroll, online employee scheduling software, employee time clock software, online job costing software, workforce management, flexible scheduling solutions, easy scheduling solutions, track employee attendance, monitor employee attendance, employee time clock, employee scheduling, true real-time time sheets, accruals and time banks, payroll system, time management system"/>
	<meta name="Description" content="Workforce Management Software for tracking employee time and attendance, employee time clock software, employee scheduling software and payroll software all in a single package. Also calculate complex over time and premium time business policies and can identify labor costs attributed to branches and departments. Managers can now track and monitor their workforce easily."/>

	<script async src="./framework/stacktrace.js"></script>
	<script src="global/Debug.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="global/CookieSetting.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="global/APIGlobal.js.php?disable_db=1&v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="framework/jquery.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="framework/backbone/underscore-min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="framework/backbone/backbone-min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="global/Global.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="./framework/html5shiv.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<script src="./framework/respond.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<![endif]-->
    <script src="./framework/bootstrap/js/bootstrap.min.js?v=<?php echo APPLICATION_BUILD?>"></script>
	<link rel="stylesheet" type="text/css" href="./framework/bootstrap/css/bootstrap.min.css?v=<?php echo APPLICATION_BUILD?>">
	<style rel="stylesheet" type="text/css">
		body {
			min-height: 100%;
			height: auto;
			width: 100%;
			position: absolute;
		}
		.footer {
			/*min-height: 68px;*/
			height: 68px;
			width: 100%;
			padding: 14px 0 14px;
			background-color: #262626;
			text-align: center;
			padding-top: 24px;
			margin-top: 40px;
			position: absolute;
			bottom: 0;
			left: 0;
		}
		.footer .footer-menu a:hover {
			text-decoration: none;
			cursor: pointer;
		}
		.footer .footer-copyright {
			color: #787878;
			margin: 0;
		}
		.company-logo img {
			max-height: 51px;
		}
		.navbar-DownForMaintenance {
			margin: 5px -15px 5px -15px;
		}
		.navbar-DownForMaintenance .company-logo img {
			max-height: 51px;
		}
		#contentBox {
			background: #fff;
			margin: 0 auto;
			position: relative;
			left: 0;
			padding: 0;
			/*width: 600px;*/
			max-width: 768px;
			/*padding: 15px;*/
		}
		#contentBox-DownForMaintenance, #contentBox-ConfirmEmail, #contentBox-ForgotPassword {
			background: #fff;
			margin: 0 auto;
			position: relative;
			left: 0;
			/*padding: 20px;*/
			/*width: 600px;*/
			border: 1px solid #779bbe;
			text-align: center;
		}
		.textTitle2 {
			color: #036;
			font-size: 16px;
			font-weight: bold;
			padding: 0;
			padding-left: 10px;
			margin: 0;
		}
        #contentBox-ForgotPassword .form-control-static {
            text-align: left;
        }
        #contentBox-DownForMaintenance .textTitle2, #contentBox-ConfirmEmail .textTitle2,#contentBox-ForgotPassword .textTitle2 {
            padding-left: 0;
            margin: 0;
            height: 60px;
            background: rgb(49,84,130);
            line-height: 60px;
            color: #fff;
        }
        /*#contentBox-ForgotPassword .textTitle2 {*/
            /*padding-left: 0;*/
            /*margin: 0;*/
            /*height: 60px;*/
            /*background: rgb(49,84,130);*/
            /*line-height: 60px;*/
            /*color: #fff;*/
            /*margin-bottom: 15px;*/
        /*}*/
        #contentBox-ForgotPassword .form-horizontal {
            margin: 15px;
        }
        #contentBox-ForgotPassword label {
            color: rgb(49,84,130);
        }
        @media (max-width: 767px) {
            #contentBox-ForgotPassword label {
                text-align: left;
            }
        }
        #contentBox-ForgotPassword .form-control {
            border-color: rgb(49,84,130);;
        }
        #contentBox-ForgotPassword .button {
            background: rgb(49,84,130);
            color: #FFFFFF;
        }

		#rowWarning {
			margin: 15px 30px;
			padding: 5px;
			border: 0px solid #c30;
			background: #FFFF00;
			font-weight: bold;
		}
        #contentBox-DownForMaintenance #rowWarning, #contentBox-ConfirmEmail #rowWarning, #contentBox-ForgotPassword #rowWarning{
            margin: 0;
            padding-top: 20px;
            padding-bottom: 20px;
            background: #FFFFFF;
            /*line-height: 60px;*/
            /*height: 60px;*/
        }
		#rowError {
			margin: 15px 30px;
			padding: 5px;
			border: 0px solid #c30;
			background: #FF0000;
			font-weight: bold;
		}
	</style>
</head>
<body>
<div id="topContainer" class="top-container">
	<nav class="navbar navbar-default">
		<div class="container">
			<div class="navbar-DownForMaintenance">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<a tabindex="-1" class="company-logo" href="https://<?php echo ORGANIZATION_URL; ?>">
						<img src="<?php if ( isset($exception) AND $exception == 'dberror' ) { ?>
							<?php echo Environment::getImagesURL()/timetrex_logo_wbg_small2.png ?>
						<?php } else { ?>
							<?php echo Environment::getBaseURL() ;?>/send_file.php?disable_db=1&object_type=primary_company_logo
						<?php } ?>
						" alt="<?php echo ORGANIZATION_NAME; ?>">
					</a>
				</div>
			</div>
		</div><!-- /.container-fluid -->
	</nav>
</div>
