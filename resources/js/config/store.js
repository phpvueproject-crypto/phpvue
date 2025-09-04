import Vue from 'vue';
import Vuex from 'vuex';
import _ from 'lodash';

Vue.use(Vuex);
let token = document.head.querySelector('meta[name="csrf-token"]');
const store = new Vuex.Store({
    state: {
        pageLoading: false,
        token: token.content,
        lang: null,
        regionMgmtId: null
    },
    mutations: {
        ['UPDATE_PAGE_LOADING'](state, pageLoading) {
            state.pageLoading = pageLoading;
        },
        ['UPDATE_TOKEN'](state, token) {
            state.token = token;
        },
        ['UPDATE_LANG'](state, value) {
            state.lang = value;
        },
        ['UPDATE_REGION_MGMT_ID'](state, value) {
            state.regionMgmtId = value;
        }
    },
    getters: {
        token(state) {
            return state.token;
        },
        region(state) {
            return state.region;
        }
    },
    modules: {
        user: {
            namespaced: true,
            state: {
                user: {}
            },
            getters: {
                isLogin: (state) => {
                    // noinspection RedundantConditionalExpressionJS
                    return typeof state.user.id !== 'undefined' ? true : false;
                },
                user: state => state.user,
                permissions(state, getters) {
                    if(!getters.isLogin)
                        return [];

                    const roles = state.user.roles;
                    let permissions = [];
                    for(let i = 0; i < roles.length; i++) {
                        const role = roles[i];
                        permissions = permissions.concat(_.map(role.permissions, 'name'));
                    }
                    return permissions;
                },
                roles(state, getters) {
                    if(!getters.isLogin)
                        return [];

                    return _.map(state.user.roles, 'name');
                },
                deviceId(state, getters) {
                    if(!getters.isLogin)
                        return null;

                    return state.user.device ? state.user.device.id : null;
                }
            },
            mutations: {
                ['UPDATE_USER'](state, user) {
                    if(user) {
                        state.user = user;
                    } else {
                        state.user = {};
                    }
                }
            }
        },
        vertex: {
            namespaced: true,
            state: {
                vertices: []
            },
            getters: {
                vertices: (state) => {
                    return state.vertices;
                }
            },
            mutations: {
                ['UPDATE_VERTICES'](state, vertices) {
                    state.vertices = vertices;
                }
            }
        },
        edge: {
            namespaced: true,
            state: {
                edges: []
            },
            getters: {
                edges: (state) => {
                    return state.edges;
                }
            },
            mutations: {
                ['UPDATE_EDGES'](state, edges) {
                    state.edges = edges;
                }
            }
        },
        location: {
            namespaced: true,
            state: {
                locations: []
            },
            getters: {
                locations: (state) => {
                    return state.locations;
                }
            },
            mutations: {
                ['UPDATE_LOCATIONS'](state, locations) {
                    state.locations = locations;
                }
            }
        },
        device: {
            namespaced: true,
            state: {
                device: null
            },
            getters: {
                device: state => state.device
            },
            mutations: {
                ['UPDATE_DEVICE'](state, device) {
                    if(device) {
                        state.device = device;
                    } else {
                        state.device = null;
                    }
                }
            }
        }
    }
});

export default store;
