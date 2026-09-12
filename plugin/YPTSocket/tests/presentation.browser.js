// Loaded by the local browser fixture with the real jQuery and socket scripts.
$(async function () {
    const results = [];
    const check = (ok, name) => { if (!ok) throw Error(name); results.push(name); };
    try {
        $('body').append('<span class="users_id_7 offline"></span><span class="users_id_42"></span><span class="users_id_0"></span>');
        const users = Array.from({ length: 1000 }, (_, users_id) => ({ users_id }));
        users.find = () => { throw Error('Repeated linear presence lookup'); };
        window.isUserOnline = () => { throw Error('Snapshot must use its presence index'); };
        socketApplyOnlineUsers(users);
        check($('.users_id_7').hasClass('online') && $('.users_id_0').hasClass('online'), 'large snapshot includes users and visitors without repeated lookup');
        socketApplyOnlineUsers({ 7: false, 42: { users_id: 42 } });
        check($('.users_id_7').hasClass('offline') && $('.users_id_42').hasClass('online') && $('.users_id_0').hasClass('offline'), 'legacy maps and departed users keep correct state');
        $('body').append('<span id="late-user" class="users_id_42 offline"></span>');
        setInitialOnlineStatus();
        check($('#late-user').hasClass('online'), 'newly inserted user elements receive current presence');
        window.isUserOnline = id => id === 7;
        setUserOnlineStatus(7);
        check($('.users_id_7').hasClass('online'), 'existing single-user callers retain their lookup behavior');
        socketApplyOnlineUsers([]);
        check($('.users_id_42').hasClass('offline'), 'empty snapshot clears online state');

        $('body').append('<div id="counters" class="text-success"><span class="total_on total_on_videos_id_8">9</span><span class="total_on total_on_videos_id_9">8</span></div>');
        socketAutoUpdateOnHTML({ total_on_videos_id_8: 3 }); AutoUpdateOnHTMLTimer();
        check($('.total_on_videos_id_8').text() === '3' && $('.total_on_videos_id_9').text() === '0', 'counter update preserves missing-counter reset');
        check(!$('#counters').hasClass('text-success'), 'counter highlight reset preserved');
        const mutations = new MutationObserver(() => {});
        mutations.observe(document.getElementById('counters'), { childList: true, subtree: true, characterData: true });
        socketAutoUpdateOnHTML({ total_on_videos_id_8: 3 }); AutoUpdateOnHTMLTimer();
        check(mutations.takeRecords().length === 0, 'identical snapshot causes no counter text writes');
        $('#counters').append('<span id="late-counter" class="total_on total_on_videos_id_8">0</span>');
        socketAutoUpdateOnHTML({ total_on_videos_id_8: 3 }); AutoUpdateOnHTMLTimer();
        check($('#late-counter').text() === '3', 'new counter receives unchanged current value');
        mutations.disconnect();

        socketInfoMinimize(); socketInfoSetStatus('connected');
        const card = (id) => ({ users_id: id, resourceId: 'page-' + id, selfURI: 'https://vlu.me/video/' + id, page_title: 'Page ' + id, identification: 'Viewer ' + id });
        yptSocketResponse = { users_id_online: [card(7)], autoUpdateOnHTML: { total_devices_online: 12 } };
        await parseSocketResponse();
        check($('#socketUsersURI a').length === 0 && socketUserCardsDirty, 'collapsed panel defers page-card rendering');
        check($('#socketInfoOnlineCount').text() === '12' && !document.getElementById('socketInfoOnline').hidden, 'compact online count stays current');
        yptSocketResponse = { users_id_online: [card(42)] }; await parseSocketResponse();
        socketInfoMaximize();
        check($('#socketUsersURI a').length === 1 && $('#socketUser42').length === 1 && !$('#socketUser7').length, 'opening renders only the newest received snapshot');
        yptSocketResponse = { users_id_online: [] }; await parseSocketResponse();
        check($('#socketUsersURI a').length === 0, 'open panel removes departed users');
        Object.defineProperty(document, 'hidden', { configurable: true, value: true });
        yptSocketResponse = { users_id_online: [card(7)] }; await parseSocketResponse();
        check($('#socketUsersURI a').length === 0, 'background tab defers page-card rendering');
        Object.defineProperty(document, 'hidden', { configurable: true, value: false });
        document.dispatchEvent(new Event('visibilitychange'));
        check($('#socketUser7').length === 1 && !socketUserCardsDirty, 'returning to the tab renders the pending snapshot');
        const panel = document.getElementById('socketInfoPanel'); panel.removeAttribute('id');
        Object.defineProperty(document, 'hidden', { configurable: true, value: true });
        yptSocketResponse = { users_id_online: [card(42)] }; await parseSocketResponse();
        check($('#socketUser42').length === 1 && !$('#socketUser7').length, 'legacy layouts keep eager rendering without a panel visibility handler');
        panel.id = 'socketInfoPanel';
        Object.defineProperty(document, 'hidden', { configurable: true, value: false });
        document.body.innerHTML = '<pre id="test-results">PASS ' + JSON.stringify(results) + '</pre>';
    } catch (error) {
        document.body.innerHTML = '<pre id="test-results">FAIL ' + error.message + '</pre>';
    }
});
