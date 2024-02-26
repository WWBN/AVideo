<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$email = '';
$name = '';
if (User::isLogged()) {
    $name = User::getNameIdentification();
    $email = User::getEmail_();
}
$metaDescription = " Contact Form";

$_page = new Page(array('Contact Us'));
?>
<style>
    .form-control:focus {
        border-color: #5cb85c;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(92, 184, 92, .6);
    }
</style>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-body">
            <form class="form-horizontal" action=" " method="post" id="contact_form">
                <input type="hidden" name="contactForm" value="1" />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>
                            <i class="fa-solid fa-envelope-open-text"></i>
                            <?php echo __("Contact Us Today!"); ?>
                        </h1>
                    </div>
                    <div class="panel-body">
                        <!-- Text input-->

                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __("Name"); ?></label>
                            <div class="col-md-9 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    <input name="first_name" placeholder="<?php echo __("Name"); ?>" class="form-control" value="<?php echo $name; ?>" type="text" required="true">
                                </div>
                            </div>
                        </div>


                        <!-- Text input-->
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __("E-mail"); ?></label>
                            <div class="col-md-9 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                    <input name="email" id="formEmail" placeholder="<?php echo __("E-mail Address"); ?>" class="form-control" value="<?php echo $email; ?>" type="email" required="true">
                                </div>
                            </div>
                        </div>


                        <!-- Text input-->
                        <div class="form-group <?php echo empty($advancedCustom->doNotShowWebsiteOnContactForm) ? "" : "hidden" ?>">
                            <label class="col-md-3 control-label"><?php echo __("Website"); ?></label>
                            <div class="col-md-9 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
                                    <input name="website" placeholder="<?php echo __("Website"); ?>" class="form-control" type="text">
                                </div>
                            </div>
                        </div>
                        <!-- Text area -->
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __("Message"); ?></label>
                            <div class="col-md-9 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                    <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>" required="true"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __("Type the code"); ?></label>
                            <div class="col-md-9 inputGroupContainer">
                                <?php
                                $capcha = getCaptcha();
                                echo $capcha['content'];
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fa-solid fa-envelope-circle-check"></i>
                            <?php echo __("Send"); ?>
                        </button>
                        <div style="display: none;" id="messageSuccess">
                            <div class="alert alert-success clear clearfix">
                                <h1 class="text-center">
                                    <i class="fa-solid fa-envelope-circle-check"></i>
                                    <?php echo __("Your message has been sent!"); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script>
    $(document).ready(function() {

        $('#contact_form').submit(function(evt) {
            evt.preventDefault();
            if (!this.checkValidity()) {
                return false;
            }
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'sendEmail',
                data: $('#contact_form').serializeArray(),
                type: 'post',
                success: function(response) {
                    modal.hidePleaseWait();
                    if (!response.error) {
                        avideoAlertSuccess(__("Your message has been sent!"));

                        $("#contact_form").hide();
                        $("#messageSuccess").fadeIn();
                    } else {
                        avideoAlertError(response.error);
                    }
                    <?php echo $capcha['btnReloadCapcha']; ?>
                }
            });
            return false;
        });

    });
</script>
<?php
$_page->print();
?>