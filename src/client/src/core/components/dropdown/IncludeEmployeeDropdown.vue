
<template>
  <oxd-input-field
    type="select"
    :label="$t('pim.include')"
    :options="includeOpts"
    :show-empty-selector="false"
  />
</template>

<script>
export default {
  name: 'IncludeEmployeeDropdown',
  data() {
    return {
      includeOpts: [
        {
          id: 1,
          param: 'onlyCurrent',
          label: this.$t('general.current_employees_only'),
        },
        {
          id: 2,
          param: 'currentAndPast',
          label: this.$t('general.current_and_past_employees'),
        },
        {
          id: 3,
          param: 'onlyPast',
          label: this.$t('general.past_employees_only'),
        },
      ],
    };
  },
};
</script>
