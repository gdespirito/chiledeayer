<script lang="ts">
import { computed, defineComponent, h } from 'vue';
import type { PropType } from 'vue';

export default defineComponent({
    name: 'JsonLd',
    props: {
        schema: {
            type: [Object, Array] as PropType<
                Record<string, unknown> | Record<string, unknown>[]
            >,
            required: true,
        },
    },
    setup(props) {
        const json = computed(() => JSON.stringify(props.schema));
        return () =>
            h('script', { type: 'application/ld+json', innerHTML: json.value });
    },
});
</script>
