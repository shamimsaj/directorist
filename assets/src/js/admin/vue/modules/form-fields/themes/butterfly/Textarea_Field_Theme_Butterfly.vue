<template>
    <div class="cptm-form-group" :class="formGroupClass">
        <div class="atbdp-row">
            <div class="atbdp-col atbdp-col-4">
                <label v-if="( label.length )">
                    <component :is="labelType">{{ label }}</component>
                </label>
                
                <p class="cptm-form-group-info" v-if="description.length" v-html="description"></p>
            </div>
            
            <div class="atbdp-col atbdp-col-8">
                <textarea name="" id="" :cols="cols" :rows="rows" :placeholder="placeholder" class="cptm-form-control" v-model="local_value"></textarea>

                <form-field-validatior 
                    :section-id="sectionId"
                    :field-id="fieldId"
                    :root="root"
                    :value="local_value" 
                    :rules="rules" 
                    v-model="validationLog" 
                    @validate="$emit( 'validate', $event )"
                />
            </div>
        </div>
    </div>
</template>

<script>
import textarea_feild from './../../../../mixins/form-fields/textarea-field';

export default {
    name: 'textarea-field-theme-butterfly',
    mixins: [ textarea_feild ],
}
</script>