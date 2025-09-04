<template>
    <div class="ui-widget-content"
         @mouseup="$emit('mouseup')"
         @click="$emit('click')">
        <slot></slot>
    </div>
</template>

<script>
import ResizeSensor from 'resize-sensor';

export default {
    name: 'Drag',
    props: {
        containment: {
            type: String
        },
        dragAfterReset: {
            type: Boolean,
            default: true
        },
        scale: {
            type: Number,
            default: 1
        },
        disabled: {
            type: Boolean,
            default: false
        },
        diameter: {
            type: Number,
            default: 20
        }
    },
    watch: {
        scale() {
            this.refreshPosition();
        },
        disabled() {
            this.init();
        }
    },
    data() {
        return {
            coordinates: {
                x: null,
                y: null
            },
            position: [0, 0, 0, 0]
        };
    },
    mounted() {
        const that = this;
        new ResizeSensor($(this.containment), function() {
            that.refreshPosition();
        });
        this.refreshPosition();
    },
    methods: {
        init() {
            const that = this;
            $(this.$el).draggable({
                disabled: that.disabled,
                containment: this.position,
                appendTo: this.dragAfterReset ? this.containment : null,
                helper: this.dragAfterReset ? 'clone' : null,
                drag: function(event, ui) {
                    const changeLeft = ui.position.left - ui.originalPosition.left;
                    const changeTop = ui.position.top - ui.originalPosition.top;
                    ui.position.left = ui.originalPosition.left + changeLeft / that.scale;
                    ui.position.top = ui.originalPosition.top + changeTop / that.scale;
                    that.$emit('drag', {
                        x: ui.position.left,
                        y: ui.position.top
                    });
                    ui.position.left = ui.originalPosition.left + changeLeft / that.scale - (that.diameter / 2);
                    ui.position.top = ui.originalPosition.top + changeTop / that.scale - (that.diameter / 2);
                },
                start: function(event, ui) {
                    ui.position.left = 0;
                    ui.position.top = 0;
                    if(that.dragAfterReset) {
                        $(this).hide();
                    }
                },
                stop: function(event, ui) {
                    const $this = $(this);
                    if(that.dragAfterReset) {
                        $(this).show();
                        $this.removeAttr('style');
                        $this.css({
                            position: 'relative'
                        });
                    } else {
                        $this.css({
                            position: 'absolute'
                        });
                    }
                    that.$emit('stop', {
                        x: ui.position.left + (that.diameter / 2),
                        y: ui.position.top + (that.diameter / 2)
                    });
                }
            });
        },
        refreshPosition() {
            if($(this.containment).length == 0)
                return;

            const position = [
                $(this.containment).offset().left,
                $(this.containment).offset().top,
                $(this.containment).offset().left + ($(this.containment).width() - $(this.$el).width() * this.scale) + this.diameter,
                $(this.containment).offset().top + ($(this.containment).height() - $(this.$el).height() * this.scale) + this.diameter
            ];

            if(!position.equal(this.position)) {
                this.position = position;
                this.init();
            }
        }
    }
}
</script>
