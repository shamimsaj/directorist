import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex)

export default new Vuex.Store({
  // state
  state: {
    active_nav_index: 0,
    fields: {},
    layouts: {},
    options: {},
    config: {},
    cached_fields: {},
    metaKeys: {},
    deprecatedMetaKeys: [],
    sidebarNavigation: {},
  },
  
  // mutations
  mutations: {
    prepareNav: ( state ) => {
      let menu_count = 0;

      for ( let menu_key in state.layouts ) {

        let status = ( 0 === menu_count ) ? true : false;
        Vue.set( state.layouts[ menu_key ], 'active', status );
    
        if ( state.layouts[ menu_key ].submenu ) {
            let submenu_count = 0;
            for ( let submenu_key in state.layouts[ menu_key ].submenu ) {

              let status = ( 0 === menu_count && 0 === submenu_count ) ? true : false;
              Vue.set( state.layouts[ menu_key ].submenu[ submenu_key ], 'active', status );
              submenu_count++;
            }
        }

        menu_count++;
      }
    },

    cacheFieldsData: ( state, payload ) => {
      state.cached_fields = payload.fields_data;
    },

    updateCachedFieldData: ( state, payload ) => {
      state.cached_fields[ payload.key ] = payload.value;
    },

    swichToNav( state, payload ) {
      let menu_key    = payload.menu_key;
      let submenu_key = payload.submenu_key;

      if ( ! state.layouts[ menu_key ] ) { return; }

      // Active Top Menu
      for ( let menu in state.layouts ) {
        Vue.set( state.layouts[ menu ], 'active', false );
      }

      Vue.set( state.layouts[ menu_key ], 'active', true );

      // Active Sub Menu
      if ( ! submenu_key && state.layouts[ menu_key ].submenu ) {
        let submenu_keys = Object.keys( state.layouts[ menu_key ].submenu );
        submenu_key = ( Array.isArray( submenu_keys ) ) ? submenu_keys[0] : null; 
      }

      window.location.hash = '#' + menu_key;
      if ( ! submenu_key ) { return; }

      for ( let submenu in state.layouts[ menu_key ].submenu ) {
        Vue.set( state.layouts[ menu_key ].submenu[ submenu ], 'active', false );
      }

      Vue.set( state.layouts[ menu_key ].submenu[ submenu_key ], 'active', true );
      window.location.hash = '#' + menu_key + '__' + submenu_key;
    },

    swichNav: ( state, index ) => {
      state.active_nav_index = index;
    },

    setMetaKey: ( state, payload ) => {
      Vue.set( state.metaKeys, payload.key, payload.value );
    },

    removeMetaKey: ( state, payload ) => {
      Vue.delete( state.metaKeys, payload.key );
    },

    updateOptionsField: ( state, payload ) => {
      state.options[ payload.field ].value = payload.value;
    },

    updateFields: ( state, value ) => {
      state.fields = value;
    },

    updatelayouts: ( state, value ) => {
      state.layouts = value;
    },

    updateOptions: ( state, value ) => {
      state.options = value;
    },

    updateConfig: ( state, value ) => {
      state.config = value;
    },

    updateFormFields: ( state, value ) => {
      state.form_fields = value;
    },

    updateFieldValue: ( state, payload ) => {
      Vue.set( state.fields[ payload.field_key ], 'value' , payload.value );
    },

    updateGeneralSectionData: ( state, payload ) => {
      state.layouts.general.submenu.general.sections[ payload.section_key ].fields[ payload.field_key ].value = payload.value;
    },
  },

  getters: {
    getFieldsValue: state => {
      const maybeJSON = function ( data ) {
          let value = ( typeof data === 'undefined' ) ? '' : data;
          if ( 'object' === typeof value ) {
              value = JSON.stringify( value );
          }

          return value;
      };

      let form_data = {};
      for ( let field in state.fields ) {
          form_data[ field ] = maybeJSON( state.fields[ field ].value )
      }

      return form_data;
    }
  }

});