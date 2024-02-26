<div id="verifyEmailModal" class="modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">Verify Email Address</h4>
            </div>
            <form id="verifyEmailForm">
                
                <input type="hidden" name="expireTime" value="0">
                <input type="hidden" name="resendTime" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Enter Code </label> 
                        <span><small>(Code will expire in <b id="expireTimer">05:00</b>)</small></span>
                        <input type="text" name="code" class="form-control">
                        <small><b class="text-danger">* Code will only last 5 minutes</b></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left" id="resendCode">Resend Code</button>
                    <h3 class="pull-left" id="resendCodeDisplay" style="margin-left: 10px; display: none;"><small><b id="resendTimer">02:00</b> to resend </small></h3>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="inactiveIndexModal" class="modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <h3>
                    Your index is currently <b class="text-danger">INACTIVE</b>.<br><br> Please check your platform contents to make sure it doesn't violate the terms and condition before indexing.
                </h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info pull-left" id="wwbnIndexTaCBtn">Terms and Condition</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="wwbnIndexReIndexBtn">Re-Index</button>
            </div>
        </div>
    </div>
</div>

<div id="indexTaCModal" class="modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">Terms and Conditions</h4>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 210px); overflow-y: auto;">
                <input type="hidden" name="wwbnIndexTaCTitle">
                <input type="hidden" name="wwbnIndexTaCDescription">
                <div id="wwbnIndexTaCDisplay"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="feedIndexModal" class="modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title"></h4>
            </div>
            <form id="feedIndexForm">
                <div class="modal-body">
                    <p><b class="text-info" id="engine_name_exist"></b> already exist</p>
                    <div class="form-group">
                        <label>Enter new Engine Name</label>
                        <input type="text" name="engine_name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>