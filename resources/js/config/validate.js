import {Validator} from 'vee-validate';
import store from "./store.js";
import _ from 'lodash';
import axios from 'axios';

Validator.extend('vertex_duplicate', {
    validate: async(value, vertex) => {
        let vertices = store.getters['vertex/vertices'];
        const vertexId = vertex.id;
        vertices = _.filter(vertices, (r) => {
            return r.id != vertexId;
        });
        const otherVertex = _.find(vertices, (r) => {
            return r.name == value;
        });
        if(otherVertex) {
            return false;
        }
        const form = {
            name: value,
            region_mgmt: {
                id: vertex.region_mgmt_id
            },
            check_status: -9
        };
        let res;
        if(!isInt(vertexId)) {
            res = await axios.post(`/api/vertices`, form);
        } else {
            res = await axios.patch(`/api/vertices/${vertexId}`, form);
        }
        res = res.data;
        return res.status != -9;
    }
});

Validator.extend('edge_duplicate', {
    validate: async(value, edge) => {
        let edges = store.getters['edge/edges'];
        const edgeId = edge.id;
        edges = _.filter(edges, (r) => {
            return r.id != edgeId;
        });
        const otherEdge = _.find(edges, (r) => {
            return r.name == value;
        });
        if(otherEdge) {
            return false;
        }
        const form = {
            name: value,
            region_mgmt: {
                id: edge.region_mgmt_id
            },
            check_status: -7
        };
        let res = null;
        if(!isInt(edgeId)) {
            res = await axios.post(`/api/edges`, form);
        } else {
            res = await axios.patch(`/api/edges/${edgeId}`, form);
        }
        res = res.data;
        return res.status != -7;
    }
});

Validator.extend('location_duplicate', {
    validate: async(value, location) => {
        let locations = store.getters['location/locations'];
        const locationId = location.id;
        locations = _.filter(locations, (r) => {
            return r.id != locationId;
        });
        const otherLocation = _.find(locations, (r) => {
            return r.device_name == value;
        });
        if(otherLocation) {
            return false;
        }
        const form = {
            device_name: value,
            room: location.room
        };
        let res;
        if(!isInt(locationId)) {
            res = await axios.post(`/api/locations`, form);
        } else {
            res = await axios.patch(`/api/locations/${locationId}`, form);
        }
        res = res.data;
        return res.status != -7;
    }
});

Validator.extend('vertex_deploy', {
    validate: async(value, vertex) => {
        const vertexId = vertex.id;
        const form = {
            check_status: -16
        };
        let res = await axios.patch(`/api/vertices/${vertexId}`, form);
        res = res.data;
        return res.status != 16;
    }
});

Validator.extend('edge_deploy', {
    validate: async(value, edge) => {
        const edgeId = edge.id;
        const form = {
            check_status: -16
        };
        let res = await axios.patch(`/api/edges/${edgeId}`, form);
        res = res.data;
        return res.status != -16;
    }
});

Validator.extend('vehicle_route_not_found', {
    validate: async(value, vehicleRoute) => {
        const vehicleRouteId = vehicleRoute.id;
        try {
            await axios.get(`/api/vehicleRoutes/${vehicleRouteId}`);
        } catch(e) {
            return false;
        }
        return true;
    }
});

Validator.extend('vehicle_route_background_not_found', {
    validate: async(value, vehicleRoute) => {
        const vehicleRouteId = vehicleRoute.id;
        try {
            let res = await axios.get(`/api/vehicleRoutes/${vehicleRouteId}?status=-17`);
            res = res.data;
            if(res.status == -17) {
                return false;
            }
        } catch(e) {
        }
        return true;
    }
});

function isInt(value) {
    return !isNaN(value) && parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
}
