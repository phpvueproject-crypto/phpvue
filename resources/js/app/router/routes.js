export default [
    {
        path: '/',
        component: require('../components/Layouts/AuthLayout').default,
        meta: {guest: true},
        children: [
            {path: '', component: require('../components/User/LoginPage').default}
        ]
    },
    {
        path: '/',
        component: require('../components/Layouts/AppLayout').default,
        meta: {auth: true, history: true},
        children: [
            {path: 'users', component: require('../components/User/UserListPage').default},
            {
                path: 'vehicleMgmts', component: require('../components/VehicleMgmt/VehicleMgmtListPage').default,
                props: {
                    showEditBtn: true
                }
            },
            {path: 'objectMgmts', component: require('../components/ObjectMgmt/ObjectMgmtListPage').default},
            {path: 'regionMgmts', component: require('../components/RegionMgmt/RegionMgmtListPage').default},
            {path: 'events', component: require('../components/Event/EventListPage').default},
            {
                path: 'parkingLotMgmts',
                component: require('../components/ParkingLotMgmt/ParkingLotMgmtListView').default,
                props: {
                    showEditBtn: true,
                    showCreateBtn: true,
                    titleText: '停車位管理'
                }
            },
            {path: 'projects', component: require('../components/Project/ProjectListPage').default},
            {
                path: 'acceptanceGrades',
                component: require('../components/AcceptanceGrade/AcceptanceGradeListPage').default
            },
            {path: 'microOrganisms', component: require('../components/MicroOrganism/MicroOrganismListPage').default},
            {
                path: 'scheduledMissions',
                component: require('../components/ScheduledMission/ScheduledMissionListPage').default
            },
            {path: 'floorRegionMgmts/:id', component: require('../components/RegionMgmt/FloorRegionListPage').default},
            {path: 'regionMgmts/:id', component: require('../components/RegionMgmt/RegionMgmtPage').default},
            {path: 'dashboard', component: require('../components/Dashboard/DashboardListPage').default},
            {path: 'devices', component: require('../components/Device/DeviceListPage').default},
            {path: 'mirStatuses', component: require('../components/MirStatus/MirStatusListPage').default},
            {path: 'missions', component: require('../components/Mission/MissionListPage').default},
            {
                path: 'missionQueues/history',
                component: require('../components/MissionQueue/MissionQueueListPage').default,
                props: {
                    viewMode: 1
                }
            },
            {
                path: 'missionQueues/current',
                component: require('../components/MissionQueue/MissionQueueListPage').default,
                props: {
                    viewMode: 0
                }
            },
            {path: 'maps', component: require('../components/Map/MapListPage').default},
            {path: 'hookStatuses', component: require('../components/HookStatus/HookStatusListPage').default},
            {
                path: 'missionBookings',
                component: require('../components/MissionBooking/MissionBookingListPage').default
            },
            {
                path: 'missionQueues/sample',
                component: require('../components/MissionQueue/MissionQueueSampleListPage.vue').default
            },
            {
                path: 'remoteManagementSystemStatuses',
                component: require('../components/RemoteManagementSystemStatus/RemoteManagementSystemStatusListPage').default
            }
        ]
    },
    {
        path: '/vehicleMgmts/window',
        meta: {auth: true},
        component: require('../components/VehicleMgmt/VehicleMgmtListPage').default,
        props: {
            tableFontSize: 16,
            showStatus: true
        }
    },
    {
        path: '/parkingLotMgmts/window',
        meta: {auth: true},
        component: require('../components/ParkingLotMgmt/ParkingLotMgmtListView').default,
        props: {
            tableFontSize: 16
        }
    },
    {
        path: '/cleanAreas/window',
        meta: {auth: true},
        component: require('../components/CleanArea/CleanAreaListView').default
    },
    {
        path: '/elevatorMgmts/window',
        meta: {auth: true},
        component: require('../components/ElevatorMgmt/ElevatorMgmtListView').default,
        props: {viewType: 'status'}
    },
    {
        path: '/mqttCommands/window',
        meta: {auth: true},
        component: require('../components/MqttCommand/MqttCommandListView').default
    },
    {path: '*', redirect: '/floorRegionMgmts/1'}
];
