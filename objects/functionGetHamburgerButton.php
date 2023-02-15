<?php
$svgClass = '';
$svgStyle = '';
if ($startActive) {
    $svgClass .= ' active';
}
if ($invert) {

    if(preg_match('/style=["\']/', $parameters)){
        $parameters = str_replace(['style="','style=\''], ['style="transform: scale(-1,1);', 'style=\'transform: scale(-1,1);'], $parameters );
    }else{
        $parameters .= 'style="transform: scale(-1,1);"';
    }
}
?>
<button type="button" id="<?php echo $id; ?>" <?php echo $parameters; ?>>
    <?php
    switch ($type) {
        case '1':
            ?>
            <svg class="ham hamRotate ham1 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 30,33 h 40 c 0,0 9.044436,-0.654587 9.044436,-8.508902 0,-7.854315 -8.024349,-11.958003 -14.89975,-10.85914 -6.875401,1.098863 -13.637059,4.171617 -13.637059,16.368042 v 40" />
            <path
                class="line middle"
                d="m 30,50 h 40" />
            <path
                class="line bottom"
                d="m 30,67 h 40 c 12.796276,0 15.357889,-11.717785 15.357889,-26.851538 0,-15.133752 -4.786586,-27.274118 -16.667516,-27.274118 -11.88093,0 -18.499247,6.994427 -18.435284,17.125656 l 0.252538,40" />
            </svg>
            <?php
            break;
        case '2':
            ?>
            <svg class="ham ham2 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 70,33 h -40 c -6.5909,0 -7.763966,-4.501509 -7.763966,-7.511428 0,-4.721448 3.376452,-9.583771 13.876919,-9.583771 14.786182,0 11.409257,14.896182 9.596449,21.970818 -1.812808,7.074636 -15.709402,12.124381 -15.709402,12.124381" />
            <path
                class="line middle"
                d="m 30,50 h 40" />
            <path
                class="line bottom"
                d="m 70,67 h -40 c -6.5909,0 -7.763966,4.501509 -7.763966,7.511428 0,4.721448 3.376452,9.583771 13.876919,9.583771 14.786182,0 11.409257,-14.896182 9.596449,-21.970818 -1.812808,-7.074636 -15.709402,-12.124381 -15.709402,-12.124381" />
            </svg>
            <?php
            break;
        case '3':
            ?>
            <svg class="ham ham3 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 70,33 h -40 c -11.092231,0 11.883874,13.496726 -3.420361,12.956839 -0.962502,-2.089471 -2.222071,-3.282996 -4.545687,-3.282996 -2.323616,0 -5.113897,2.622752 -5.113897,7.071068 0,4.448316 2.080609,7.007933 5.555839,7.007933 2.401943,0 2.96769,-1.283974 4.166879,-3.282995 2.209342,0.273823 4.031294,1.642466 5.857227,-0.252538 v -13.005715 16.288404 h 7.653568" />
            <path
                class="line middle"
                d="m 70,50 h -40 c -5.6862,0 -8.534259,5.373483 -8.534259,11.551069 0,7.187738 3.499166,10.922274 13.131984,10.922274 11.021777,0 7.022787,-15.773343 15.531095,-15.773343 3.268142,0 5.177031,-2.159429 5.177031,-6.7 0,-4.540571 -1.766442,-7.33533 -5.087851,-7.326157 -3.321409,0.0092 -5.771288,2.789632 -5.771288,7.326157 0,4.536525 2.478983,6.805271 5.771288,6.7" />
            <path
                class="line bottom"
                d="m 70,67 h -40 c 0,0 -3.680675,0.737051 -3.660714,-3.517857 0.02541,-5.415597 3.391687,-10.357143 10.982142,-10.357143 4.048418,0 17.88928,0.178572 23.482143,0.178572 0,2.563604 2.451177,3.403635 4.642857,3.392857 2.19168,-0.01078 4.373905,-1.369814 4.375,-3.392857 0.0011,-2.023043 -1.924401,-2.589191 -4.553571,-4.107143 -2.62917,-1.517952 -4.196429,-1.799562 -4.196429,-3.660714 0,-1.861153 2.442181,-3.118811 4.196429,-3.035715 1.754248,0.0831 4.375,0.890841 4.375,3.125 2.628634,0 6.160714,0.267857 6.160714,0.267857 l -0.178571,-2.946428 10.178571,0 -10.178571,0 v 6.696428 l 8.928571,0 -8.928571,0 v 7.142858 l 10.178571,0 -10.178571,0" />
            </svg>
            <?php
            break;
        case '4':
            ?>
            <svg class="ham hamRotate ham4 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 70,33 h -40 c 0,0 -8.5,-0.149796 -8.5,8.5 0,8.649796 8.5,8.5 8.5,8.5 h 20 v -20" />
            <path
                class="line middle"
                d="m 70,50 h -40" />
            <path
                class="line bottom"
                d="m 30,67 h 40 c 0,0 8.5,0.149796 8.5,-8.5 0,-8.649796 -8.5,-8.5 -8.5,-8.5 h -20 v 20" />
            </svg>
            <?php
            break;
        case '5':
            ?>
            <svg class="ham hamRotate180 ham5 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 30,33 h 40 c 0,0 8.5,-0.68551 8.5,10.375 0,8.292653 -6.122707,9.002293 -8.5,6.625 l -11.071429,-11.071429" />
            <path
                class="line middle"
                d="m 70,50 h -40" />
            <path
                class="line bottom"
                d="m 30,67 h 40 c 0,0 8.5,0.68551 8.5,-10.375 0,-8.292653 -6.122707,-9.002293 -8.5,-6.625 l -11.071429,11.071429" />
            </svg>
            <?php
            break;
        case '6':
            ?>
            <svg class="ham ham6 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 30,33 h 40 c 13.100415,0 14.380204,31.80258 6.899646,33.421777 -24.612039,5.327373 9.016154,-52.337577 -12.75751,-30.563913 l -28.284272,28.284272" />
            <path
                class="line middle"
                d="m 70,50 c 0,0 -32.213436,0 -40,0 -7.786564,0 -6.428571,-4.640244 -6.428571,-8.571429 0,-5.895471 6.073743,-11.783399 12.286435,-5.570707 6.212692,6.212692 28.284272,28.284272 28.284272,28.284272" />
            <path
                class="line bottom"
                d="m 69.575405,67.073826 h -40 c -13.100415,0 -14.380204,-31.80258 -6.899646,-33.421777 24.612039,-5.327373 -9.016154,52.337577 12.75751,30.563913 l 28.284272,-28.284272" />
            </svg>
            <?php
            break;
        case '7':
            ?>
            <svg class="ham hamRotate ham7 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 70,33 h -40 c 0,0 -6,1.368796 -6,8.5 0,7.131204 6,8.5013 6,8.5013 l 20,-0.0013" />
            <path
                class="line middle"
                d="m 70,50 h -40" />
            <path
                class="line bottom"
                d="m 69.575405,67.073826 h -40 c -5.592752,0 -6.873604,-9.348582 1.371031,-9.348582 8.244634,0 19.053564,21.797129 19.053564,12.274756 l 0,-40" />
            </svg>
            <?php
            break;
        case '8':
            ?>

            <svg class="ham hamRotate ham8 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20" />
            <path
                class="line middle"
                d="m 30,50 h 40" />
            <path
                class="line bottom"
                d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20" />
            </svg>
            <?php
            break;

        default:
            ?>
            <svg class="ham hamRotate ham1 <?php echo $svgClass; ?>" style="<?php echo $svgStyle; ?>" viewBox="0 0 100 100" width="32" onclick="this.classList.toggle('active')">
            <path
                class="line top"
                d="m 30,33 h 40 c 0,0 9.044436,-0.654587 9.044436,-8.508902 0,-7.854315 -8.024349,-11.958003 -14.89975,-10.85914 -6.875401,1.098863 -13.637059,4.171617 -13.637059,16.368042 v 40" />
            <path
                class="line middle"
                d="m 30,50 h 40" />
            <path
                class="line bottom"
                d="m 30,67 h 40 c 12.796276,0 15.357889,-11.717785 15.357889,-26.851538 0,-15.133752 -4.786586,-27.274118 -16.667516,-27.274118 -11.88093,0 -18.499247,6.994427 -18.435284,17.125656 l 0.252538,40" />
            </svg>
            <?php
            break;
    }
    ?>
</button>
