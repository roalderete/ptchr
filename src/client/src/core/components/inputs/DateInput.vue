
<template>
  <oxd-input-field
    type="date"
    :placeholder="userDateFormat"
    :day-attributes="attributes"
    :events="events"
    :display-format="jsDateFormat"
    :locale="locale"
    @select-year="onSelectYear"
  />
</template>

<script>
import {onBeforeMount, reactive, toRefs} from 'vue';
import {APIService} from '@ohrm/core/util/services/api.service';
import {
  startOfYear,
  endOfYear,
  formatDate,
  parseDate,
} from '@ohrm/core/util/helper/datefns';
import useDateFormat from '@/core/util/composable/useDateFormat';
import useLocale from '@/core/util/composable/useLocale';

export default {
  name: 'DateInput',
  setup() {
    const state = reactive({
      attributes: [],
      events: [],
    });
    const http = new APIService(window.appGlobal.baseUrl, '');
    const {jsDateFormat, userDateFormat} = useDateFormat();
    const {locale} = useLocale();

    const responseValidator = (status) => {
      return (status >= 200 && status < 300) || status === 403;
    };

    const fetchWorkWeek = async () => {
      http
        .request({
          method: 'GET',
          url: '/api/v2/leave/workweek',
          params: {
            model: 'indexed',
          },
          validateStatus: responseValidator,
        })
        .then(({data}) => {
          if (data?.data) {
            state.attributes = Object.keys(data.data).map((i) => {
              return {
                index: parseInt(i),
                class:
                  data.data[i] === 8
                    ? '--non-working-day'
                    : data.data[i] === 4
                    ? '--working-day-half'
                    : '',
              };
            });
          }
        });
    };

    const fetchEvents = async (fromDate, toDate) => {
      http
        .request({
          method: 'GET',
          url: '/api/v2/leave/holidays',
          params: {
            fromDate,
            toDate,
          },
          validateStatus: responseValidator,
        })
        .then(({data}) => {
          if (Array.isArray(data?.data)) {
            state.events = data.data.map((event) => {
              return {
                date: parseDate(event.date, 'yyyy-MM-dd'),
                type: event.name,
                class: event.length === 0 ? '--holiday-full' : '--holiday-half',
              };
            });
          }
        });
    };

    const onSelectYear = async ({year}) => {
      const now = new Date().setFullYear(year);
      const fromDate = formatDate(startOfYear(now), 'yyyy-MM-dd');
      const endDate = formatDate(endOfYear(now), 'yyyy-MM-dd');
      fetchEvents(fromDate, endDate);
    };

    onBeforeMount(async () => {
      await fetchWorkWeek();
      await onSelectYear({year: new Date().getFullYear()});
    });

    return {
      jsDateFormat,
      userDateFormat,
      ...toRefs(state),
      onSelectYear,
      locale,
    };
  },
};
</script>
