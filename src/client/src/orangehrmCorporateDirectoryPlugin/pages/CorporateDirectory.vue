<template>
  <div class="orangehrm-background-container">
    <oxd-table-filter :filter-title="$t('general.directory')">
      <oxd-form @submit-valid="onSearch" @reset="onReset">
        <oxd-form-row>
          <oxd-grid :cols="3">
            <oxd-grid-item>
              <employee-autocomplete
                v-model="filters.employeeNumber"
                :rules="rules.employee"
                api-path="/api/v2/directory/employees"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.jobTitleId"
                type="select"
                :label="$t('general.job_title')"
                :options="jobTitles"
              />
            </oxd-grid-item>
            <oxd-grid-item>
              <oxd-input-field
                v-model="filters.locationId"
                type="select"
                :label="$t('general.location')"
                :options="locations"
              />
            </oxd-grid-item>
          </oxd-grid>
        </oxd-form-row>

        <oxd-divider />

        <oxd-form-actions>
          <oxd-button
            :label="$t('general.reset')"
            display-type="ghost"
            type="reset"
          />
          <submit-button :label="$t('general.search')" />
        </oxd-form-actions>
      </oxd-form>
    </oxd-table-filter>

    <br />

    <div class="orangehrm-corporate-directory">
      <div class="orangehrm-paper-container">
        <table-header
          :selected="0"
          :total="total"
          :loading="false"
          :show-divider="false"
        ></table-header>
        <div ref="scrollerRef" class="orangehrm-container">
          <oxd-grid :cols="colSize">
            <oxd-grid-item
              v-for="(employee, index) in employees"
              :key="employee"
            >
              <summary-card
                v-if="isMobile && currentIndex === index"
                :employee-id="employee.id"
                :employee-name="employee.employeeName"
                :employee-sub-unit="employee.employeeSubUnit"
                :employee-location="employee.employeeLocation"
                :employee-designation="employee.employeeJobTitle"
                :is-online="employee.isOnline"
                @click="showEmployeeDetails(index)"
              >
                <employee-details
                  :employee-id="employee.id"
                  :is-mobile="isMobile"
                >
                </employee-details>
              </summary-card>
              <summary-card
                v-else
                :employee-id="employee.id"
                :employee-name="employee.employeeName"
                :employee-sub-unit="employee.employeeSubUnit"
                :employee-location="employee.employeeLocation"
                :employee-designation="employee.employeeJobTitle"
                :is-online="employee.isOnline"
                @click="showEmployeeDetails(index)"
              >
              </summary-card>
            </oxd-grid-item>
          </oxd-grid>
          <oxd-loading-spinner
            v-if="isLoading"
            class="orangehrm-container-loader"
          />
        </div>
        <div class="orangehrm-bottom-container"></div>
      </div>

      <div
        v-if="isEmployeeSelected && isMobile === false"
        class="orangehrm-corporate-directory-sidebar"
      >
        <oxd-grid-item>
          <summary-card-details
            :employee-designation="employees[currentIndex].employeeJobTitle"
            :employee-id="employees[currentIndex].id"
            :employee-location="employees[currentIndex].employeeLocation"
            :employee-name="employees[currentIndex].employeeName"
            :employee-sub-unit="employees[currentIndex].employeeSubUnit"
            :is-online="employees[currentIndex].isOnline"
            @hide-details="hideEmployeeDetails()"
          ></summary-card-details>
        </oxd-grid-item>
      </div>
    </div>
  </div>
</template>

<script>
import {reactive, toRefs, onMounted, onBeforeUnmount} from 'vue';
import usei18n from '@/core/util/composable/usei18n';
import useToast from '@/core/util/composable/useToast';
import {APIService} from '@/core/util/services/api.service';
import {
  shouldNotExceedCharLength,
  validSelection,
} from '@/core/util/validation/rules';
import useInfiniteScroll from '@ohrm/core/util/composable/useInfiniteScroll';
import EmployeeAutocomplete from '@/core/components/inputs/EmployeeAutocomplete';
import SummaryCard from '@/orangehrmCorporateDirectoryPlugin/components/SummaryCard';
import EmployeeDetails from '@/orangehrmCorporateDirectoryPlugin/components/EmployeeDetails';
import SummaryCardDetails from '@/orangehrmCorporateDirectoryPlugin/components/SummaryCardDetails';
import {OxdSpinner, useResponsive} from '@ohrm/oxd';

const defaultFilters = {
  employeeNumber: null,
  jobTitleId: null,
  locationId: null,
};

export default {
  name: 'CorporateDirectory',

  components: {
    'summary-card': SummaryCard,
    'oxd-loading-spinner': OxdSpinner,
    'employee-details': EmployeeDetails,
    'summary-card-details': SummaryCardDetails,
    'employee-autocomplete': EmployeeAutocomplete,
  },

  props: {
    jobTitles: {
      type: Array,
      default: () => [],
    },
    locations: {
      type: Array,
      default: () => [],
    },
  },

  setup() {
    const {$t} = usei18n();
    const {noRecordsFound} = useToast();
    const responsiveState = useResponsive();

    const rules = {
      employee: [shouldNotExceedCharLength(100), validSelection],
    };

    const employeeDataNormalizer = (data) => {
      return data.map((item) => {
        return {
          id: item.empNumber,
          employeeName:
            `${item.firstName} ${item.middleName} ${item.lastName} ` +
            (item.terminationId ? $t('general.past_employee') : ''),
          employeeJobTitle: item.jobTitle?.isDeleted
            ? `${item.jobTitle?.title} ` + $t('general.deleted')
            : item.jobTitle?.title,
          employeeSubUnit: item.subunit?.name,
          employeeLocation: item.location?.name,
          // backend may provide this boolean (adjust field name if different)
          isOnline: item.isOnline || false,
        };
      });
    };

    const http = new APIService(
      window.appGlobal.baseUrl,
      '/api/v2/directory/employees',
    );

    const limit = 14;

    const state = reactive({
      total: 0,
      offset: 0,
      employees: [],
      currentIndex: -1,
      isLoading: false,
      filters: {
        ...defaultFilters,
      },
    });

    const fetchData = () => {
      state.isLoading = true;
      http
        .getAll({
          limit: limit,
          offset: state.offset,
          locationId: state.filters.locationId?.id,
          empNumber: state.filters.employeeNumber?.id,
          jobTitleId: state.filters.jobTitleId?.id,
        })
        .then((response) => {
          const {data, meta} = response.data;
          state.total = meta?.total || 0;
          if (Array.isArray(data)) {
            state.employees = [
              ...state.employees,
              ...employeeDataNormalizer(data),
            ];
            // after adding employees, refresh their online statuses
            updateOnlineFromApi();
          }
          if (state.total === 0) {
            noRecordsFound();
          }
        })
        .finally(() => (state.isLoading = false));
    };

    // Polling / update online statuses
    let onlineIntervalId = null;
    let onlineApiAvailable = true;
    const updateOnlineFromApi = () => {
      if (!onlineApiAvailable) return;
      // NOTE: Replace this endpoint with your actual backend endpoint that returns active user identifiers
      // Expected response example: { data: [123, 456, ...] } or [{ empNumber: 123 }, ...]
      fetch(`${window.appGlobal.baseUrl}/api/v2/directory/active-employees`)
        .then((res) => {
          if (!res.ok) {
            // if the endpoint is missing (404) or otherwise failing, stop polling to avoid repeated errors
            if (res.status === 404) {
              console.warn(
                'Directory active-employees endpoint not found (404). Stopping online polling.',
              );
              onlineApiAvailable = false;
              if (onlineIntervalId) {
                clearInterval(onlineIntervalId);
                onlineIntervalId = null;
              }
            } else {
              console.warn(
                `Directory active-employees endpoint returned status ${res.status}.`,
              );
            }
            // return a rejected promise to jump to catch and avoid attempting to parse non-JSON error pages
            return Promise.reject(
              new Error('active-employees endpoint not OK'),
            );
          }
          return res.json();
        })
        .then((json) => {
          const list = json?.data || [];
          const ids = new Set(
            list.map((i) => {
              if (i && typeof i === 'object')
                return i.empNumber || i.empNumber === 0
                  ? String(i.empNumber)
                  : String(i);
              return String(i);
            }),
          );
          state.employees = state.employees.map((e) => ({
            ...e,
            isOnline: ids.has(String(e.id)),
          }));
          // Debugging: log active ids and updated employee statuses
          try {
            console.debug(
              'active-ids',
              Array.from(ids),
              'employees',
              state.employees.map((x) => ({id: x.id, isOnline: x.isOnline})),
            );
          } catch (err) {
            console.debug('Debug logging failed:', err);
          }
        })
        .catch((err) => {
          // only log once when endpoint is unavailable, otherwise ignore transient errors
          if (onlineApiAvailable) {
            console.debug(
              'Error checking active employees:',
              err.message || err,
            );
          }
        });
    };

    onMounted(() => {
      // start polling every 30s (balanced update frequency and server load)
      updateOnlineFromApi();
      onlineIntervalId = setInterval(updateOnlineFromApi, 30000);
    });

    onBeforeUnmount(() => {
      if (onlineIntervalId) clearInterval(onlineIntervalId);
    });

    const {scrollerRef} = useInfiniteScroll(() => {
      if (state.employees.length >= state.total) return;
      state.offset += limit;
      fetchData();
    });

    return {
      rules,
      fetchData,
      scrollerRef,
      ...toRefs(state),
      ...toRefs(responsiveState),
    };
  },

  computed: {
    isMobile() {
      return this.windowWidth < 800;
    },
    isEmployeeSelected() {
      return this.currentIndex >= 0;
    },
    oxdGridClasses() {
      return {
        'orangehrm-container': true,
        'orangehrm-container-min-display': this.isEmployeeSelected,
      };
    },
    colSize() {
      if (this.windowWidth >= 1920) {
        return this.isEmployeeSelected ? 5 : 7;
      }
      return this.isEmployeeSelected ? 3 : 4;
    },
  },

  beforeMount() {
    this.fetchData();
  },

  methods: {
    hideEmployeeDetails() {
      this.currentIndex = -1;
    },
    showEmployeeDetails(index) {
      if (this.currentIndex != index) {
        this.currentIndex = index;
      } else {
        this.hideEmployeeDetails();
      }
    },
    onSearch() {
      this.hideEmployeeDetails();
      this.employees = [];
      this.offset = 0;
      this.fetchData();
    },
    onReset() {
      this.hideEmployeeDetails();
      this.employees = [];
      this.offset = 0;
      this.filters = {...defaultFilters};
      this.fetchData();
    },
  },
};
</script>

<style src="./corporate-directory.scss" lang="scss" scoped></style>
