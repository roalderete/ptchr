
<template>
  <oxd-input-field
    type="autocomplete"
    :label="$t('general.employee_name')"
    :clear="false"
    :create-options="loadEmployees"
  >
    <template #afterSelected="{data}">
      <template v-if="data.isPastEmployee">
        {{ $t('general.past_employee') }}
      </template>
    </template>
    <template #option="{data}">
      <span>{{ data.label }}</span>
      <div v-if="data.isPastEmployee" class="past-employee-tag">
        {{ $t('general.past_employee') }}
      </div>
    </template>
  </oxd-input-field>
</template>

<script>
import {APIService} from '@ohrm/core/util/services/api.service';
export default {
  name: 'EmployeeAutocomplete',
  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    apiPath: {
      type: String,
      default: '/api/v2/pim/employees',
    },
  },
  setup(props) {
    const http = new APIService(window.appGlobal.baseUrl, props.apiPath);
    return {
      http,
    };
  },
  methods: {
    async loadEmployees(searchParam) {
      return new Promise((resolve) => {
        if (searchParam.trim() && searchParam.length < 100) {
          this.http
            .getAll({
              nameOrId: searchParam.trim(),
              ...this.params,
            })
            .then(({data}) => {
              resolve(
                data.data.map((employee) => {
                  return {
                    id: employee.empNumber,
                    label: `${employee.firstName} ${employee.middleName} ${employee.lastName}`,
                    _employee: employee,
                    isPastEmployee: !!employee.terminationId,
                  };
                }),
              );
            });
        } else {
          resolve([]);
        }
      });
    },
  },
};
</script>

<style scoped>
.past-employee-tag {
  margin-left: auto;
}
</style>
