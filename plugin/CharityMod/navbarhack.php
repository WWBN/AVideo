<?php require_once "userMethods.php"; ?>

<script>
    var logoHtml = "";
        $(document).ready(function () {
$("#sideBarContainer ul").html("<li>Mycontent</li>"+$("#sideBarContainer ul").html());
            <?php if(!empty($_GET['orga'])) { 
                $orga = charityUser::getOrgaByName($_GET['orga']);
                $global['orgaId'] = $orga['id']; // whats sense here? mmmh..
                if(!empty($orga['photoURL'])){
            ?>
            logoHtml = '<a class="navbar-brand" href="<?php echo $global['webSiteRootURL']."charity/".$orga['user']; ?>" >
                        <img src="<?php echo $global['webSiteRootURL'], $orga['photoURL']; ?>" alt="<?php echo $orga['user']; ?>" class="img-responsive ">
                    </a>';
         $("li .navbar-brand").html(logoHtml);   
            <?php } ?>
                $("li .navbar-brand").parent().append('                <li>
                    <a class="navbar-brand" href="<?php echo $global['webSiteRootURL']."charity/".$orga['user']; ?>" >
                        <?php echo $orga['user']; ?>
                    </a> <span class="small" style="margin-left: 20px;">Already collected: $<?php echo $orga['money_amount']; ?></span>
                </li>');
            <?php }?>
        });

</script>