<style>
    body{
        background: radial-gradient(#AAA, #333);
    }
    .BokehBackground{
        position: fixed;
        width: 100vw;
        height: 100vh;
        top: 0;
        left: 0;
        z-index: -1;
    }
    .BokehBackground span {
        width: 20vmin;
        height: 20vmin;
        border-radius: 20vmin;
        backface-visibility: hidden;
        position: absolute;
        animation-name: move;
        animation-duration: 6s;
        animation-timing-function: linear;
        animation-iteration-count: infinite;
    }
    .BokehBackground span:nth-child(1) {
        color: #AAAAAA22;
        top: 57%;
        left: 77%;
        animation-duration: 15.4s;
        animation-delay: -11.4s;
        transform-origin: 12vw -1vh;
        box-shadow: 40vmin 0 6.8486152595vmin currentColor;
    }
    .BokehBackground span:nth-child(2) {
        color: #AAAAAA44;
        top: 8%;
        left: 33%;
        animation-duration: 12.8s;
        animation-delay: -14s;
        transform-origin: -14vw 10vh;
        box-shadow: -40vmin 0 12.9224625899vmin currentColor;
    }
    .BokehBackground span:nth-child(3) {
        color: #AAAAAA66;
        top: 36%;
        left: 85%;
        animation-duration: 10.9s;
        animation-delay: -3.6s;
        transform-origin: 18vw 6vh;
        box-shadow: -40vmin 0 7.1262829899vmin currentColor;
    }
    .BokehBackground span:nth-child(4) {
        color: #AAAAAA88;
        top: 50%;
        left: 38%;
        animation-duration: 11.5s;
        animation-delay: -7.7s;
        transform-origin: 11vw -20vh;
        box-shadow: -40vmin 0 6.3495034139vmin currentColor;
    }
    .BokehBackground span:nth-child(5) {
        color: #AAAAAAAA;
        top: 43%;
        left: 14%;
        animation-duration: 15.3s;
        animation-delay: -4s;
        transform-origin: -17vw -23vh;
        box-shadow: 40vmin 0 11.7463201894vmin currentColor;
    }
    .BokehBackground span:nth-child(6) {
        color: #AAAAAACC;
        top: 48%;
        left: 9%;
        animation-duration: 10.1s;
        animation-delay: -10.7s;
        transform-origin: 10vw 17vh;
        box-shadow: 40vmin 0 6.8711189259vmin currentColor;
    }
    .BokehBackground span:nth-child(7) {
        color: #AAAAAAEE;
        top: 54%;
        left: 39%;
        animation-duration: 13.3s;
        animation-delay: -9.2s;
        transform-origin: 7vw 18vh;
        box-shadow: 40vmin 0 12.9378815861vmin currentColor;
    }
    .BokehBackground span:nth-child(8) {
        color: #33333322;
        top: 5%;
        left: 66%;
        animation-duration: 11.1s;
        animation-delay: -3s;
        transform-origin: -18vw 24vh;
        box-shadow: 40vmin 0 6.9298385346vmin currentColor;
    }
    .BokehBackground span:nth-child(9) {
        color: #33333344;
        top: 85%;
        left: 52%;
        animation-duration: 15.8s;
        animation-delay: -1.3s;
        transform-origin: 7vw -4vh;
        box-shadow: 40vmin 0 13.8478637982vmin currentColor;
    }
    .BokehBackground span:nth-child(10) {
        color: #33333366;
        top: 13%;
        left: 34%;
        animation-duration: 13.8s;
        animation-delay: -4.9s;
        transform-origin: 14vw -17vh;
        box-shadow: -40vmin 0 8.5193726229vmin currentColor;
    }
    .BokehBackground span:nth-child(11) {
        color: #33333388;
        top: 14%;
        left: 70%;
        animation-duration: 11.5s;
        animation-delay: -10s;
        transform-origin: -7vw 13vh;
        box-shadow: 40vmin 0 7.028038105vmin currentColor;
    }
    .BokehBackground span:nth-child(12) {
        color: #333333AA;
        top: 33%;
        left: 94%;
        animation-duration: 11.3s;
        animation-delay: -11s;
        transform-origin: 9vw -2vh;
        box-shadow: 40vmin 0 14.1794004555vmin currentColor;
    }
    .BokehBackground span:nth-child(13) {
        color: #333333CC;
        top: 10%;
        left: 25%;
        animation-duration: 13.9s;
        animation-delay: -9.1s;
        transform-origin: -9vw 0vh;
        box-shadow: 40vmin 0 10.505461257vmin currentColor;
    }
    .BokehBackground span:nth-child(14) {
        color: #77777722;
        top: 86%;
        left: 45%;
        animation-duration: 11.7s;
        animation-delay: -8.3s;
        transform-origin: 1vw 12vh;
        box-shadow: 40vmin 0 12.6068329782vmin currentColor;
    }
    .BokehBackground span:nth-child(15) {
        color: #77777744;
        top: 27%;
        left: 37%;
        animation-duration: 15.5s;
        animation-delay: -8.3s;
        transform-origin: 16vw 15vh;
        box-shadow: 40vmin 0 8.121445529vmin currentColor;
    }
    .BokehBackground span:nth-child(16) {
        color: #77777766;
        top: 89%;
        left: 70%;
        animation-duration: 11.5s;
        animation-delay: -3.9s;
        transform-origin: 18vw -22vh;
        box-shadow: -40vmin 0 7.6200706662vmin currentColor;
    }
    .BokehBackground span:nth-child(17) {
        color: #77777788;
        top: 11%;
        left: 100%;
        animation-duration: 12.8s;
        animation-delay: -5.5s;
        transform-origin: 14vw 18vh;
        box-shadow: 40vmin 0 14.0752136262vmin currentColor;
    }
    .BokehBackground span:nth-child(18) {
        color: #777777AA;
        top: 94%;
        left: 56%;
        animation-duration: 10.7s;
        animation-delay: -12.6s;
        transform-origin: 7vw 24vh;
        box-shadow: -40vmin 0 7.6193942646vmin currentColor;
    }
    .BokehBackground span:nth-child(19) {
        color: #777777CC;
        top: 67%;
        left: 95%;
        animation-duration: 11.6s;
        animation-delay: -5s;
        transform-origin: -5vw 13vh;
        box-shadow: 40vmin 0 14.8350133335vmin currentColor;
    }
    .BokehBackground span:nth-child(20) {
        color: #777777EE;
        top: 50%;
        left: 48%;
        animation-duration: 12.8s;
        animation-delay: -3.1s;
        transform-origin: 8vw 13vh;
        box-shadow: 40vmin 0 5.0793911287vmin currentColor;
    }

    @keyframes move {
        100% {
            transform: translate3d(0, 0, 1px) rotate(360deg);
        }
    }
</style>
<div class="BokehBackground">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
</div>