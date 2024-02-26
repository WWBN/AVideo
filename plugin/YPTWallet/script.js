function updateYPTWallet() {
    $.ajax({
        url: webSiteRootURL + 'plugin/YPTWallet/getBalance.json.php',
        success: function (response) {
            $('.walletBalance').text(response.balance);
        }
    });
}

function socketWalletAddBalance(json){
    console.log('socketWalletAddBalance', json);
    walletBalance = json.balance;
    console.log('socketWalletAddBalance walletBalance', walletBalance);
    $('.walletBalance').text(walletBalance);
}