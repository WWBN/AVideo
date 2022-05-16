<li class="nav-item" style="margin-right: 0px; " id="searchNavItem">
    <div class="navbar-header">
        <button type="button" id="buttonSearch" class="visible-xs navbar-toggle btn btn-default navbar-btn faa-parent animated-hover animate__animated animate__bounceIn" data-toggle="collapse" data-target="#mysearch" style="padding: 6px 12px;">
            <span class="fa fa-search faa-shake"></span>
        </button>
    </div>
    <div class="input-group"  id="mysearch">
        <form class="navbar-form form-inline input-group" role="search" id="searchForm"  action="<?php echo $global['webSiteRootURL']; ?>" style="padding: 0;">
            <input class="form-control globalsearchfield" type="text" value="<?php
            if (!empty($_GET['search'])) {
                echo htmlentities($_GET['search']);
            }
            ?>" name="search" placeholder="<?php echo __("Search"); ?>" id="searchFormInput">
            <span class="input-group-append">
                <button class="btn btn-default btn-outline-secondary border-left-0 border  py-2 faa-parent animated-hover" type="submit">
                    <i class="fas fa-search faa-shake"></i>
                </button>
            </span>
        </form>
    </div>
</li>