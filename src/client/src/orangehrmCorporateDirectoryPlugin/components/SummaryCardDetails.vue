<template>
  <summary-card
    v-bind="$attrs"
    :employee-id="employeeId"
    :show-back-button="true"
  >
    <employee-details :employee-id="employeeId"> </employee-details>
  </summary-card>
</template>

<script>
import SummaryCard from '@/orangehrmCorporateDirectoryPlugin/components/SummaryCard';
import EmployeeDetails from '@/orangehrmCorporateDirectoryPlugin/components/EmployeeDetails';

export default {
  name: 'SummaryCardDetails',
  components: {
    'employee-details': EmployeeDetails,
    'summary-card': SummaryCard,
  },
  props: {
    employeeId: {
      type: Number,
      required: true,
    },
  },
};
</script>
