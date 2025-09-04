import store from "../../config/store";
import mixin from "../../config/mixin";

export default async(to, from, next) => {
    store.commit('UPDATE_PAGE_LOADING', true);
    await mixin.syncUser();
    const isLogin = store.getters['user/isLogin'];
    const roles = store.getters['user/roles'];
    const regionMgmtId = from.query.region_mgmt_id;
    store.commit('UPDATE_PAGE_LOADING', false);

    if(regionMgmtId) {
        window.Echo.leave(`regionMgmts.presence.${regionMgmtId}`);
        window.Echo.leave(`regionMgmts.${regionMgmtId}`);
        store.commit('UPDATE_REGION_MGMT_ID', null);
    }

    if(to.matched.some(record => record.meta.auth)) {
        if(isLogin) {
            const permissions = store.getters['user/permissions'];
            for(let i = 0; i < to.matched.length; i++) {
                const meta = to.matched[i].meta;
                if(typeof meta.permissions !== 'undefined' || typeof meta.roles !== 'undefined') {
                    let routesPermissions = [], routesRoles = [];
                    if(typeof meta.permissions !== 'undefined')
                        routesPermissions = meta.permissions;
                    if(typeof meta.roles !== 'undefined')
                        routesRoles = meta.roles;

                    if(routesPermissions.length > 0 || routesRoles.length > 0) {
                        if(!permissions.contains(routesPermissions) && !roles.contains(routesRoles)) {
                            const redirects = _.compact(_.map(to.matched, 'meta.redirect'));
                            if(redirects.length > 0) {
                                const redirect = redirects[redirects.length - 1];
                                next(redirect);
                            } else {
                                next('/');
                            }
                        }
                    }
                }

                if(i == to.matched.length - 1) {
                    next();
                }
            }
            const history = isHistory(to);
            if(history)
                localStorage.redirect = to.fullPath;
        } else {
            const redirects = _.compact(_.map(to.matched, 'meta.redirect'));
            if(redirects.length > 0) {
                const redirect = redirects[redirects.length - 1];
                next(redirect);
            } else {
                next('/');
            }
        }
    } else if(to.matched.some(record => record.meta.guest)) {
        if(isLogin) {
            next('/regionMgmts?is_deploy=1');
        } else {
            next();
        }
    } else {
        next();
    }
};

function isHistory(route) {
    for(let i = (route.matched.length - 1); i >= 0; i--) {
        const meta = route.matched[i].meta;
        if(typeof meta.history === 'undefined') {
            continue;
        }

        return meta.history;
    }
    return false;
}
