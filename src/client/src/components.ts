
import {App} from 'vue';
import SubmitButton from '@ohrm/components/buttons/SubmitButton.vue';
import TableHeader from '@ohrm/components/table/TableHeader.vue';
import RequiredText from '@ohrm/components/labels/RequiredText.vue';
import Layout from '@ohrm/components/layout/Layout.vue';
import DateInput from '@ohrm/components/inputs/DateInput.vue';
import TimeInput from '@ohrm/components/inputs/TimeInput.vue';

import {
  OxdCardTable,
  OxdButton,
  OxdPagination,
  OxdDivider,
  OxdText,
  OxdIconButton,
  OxdForm,
  OxdFormRow,
  OxdFormActions,
  OxdInputField,
  OxdInputGroup,
  OxdGrid,
  OxdGridItem,
  OxdTableFilter,
} from '@ohrm/oxd';

export default {
  install: (app: App) => {
    app.component('OxdLayout', Layout);
    app.component('OxdCardTable', OxdCardTable);
    app.component('OxdButton', OxdButton);
    app.component('OxdPagination', OxdPagination);
    app.component('OxdDivider', OxdDivider);
    app.component('OxdText', OxdText);
    app.component('OxdIconButton', OxdIconButton);
    app.component('OxdForm', OxdForm);
    app.component('OxdFormRow', OxdFormRow);
    app.component('OxdFormActions', OxdFormActions);
    app.component('OxdInputField', OxdInputField);
    app.component('OxdInputGroup', OxdInputGroup);
    app.component('OxdGrid', OxdGrid);
    app.component('OxdGridItem', OxdGridItem);
    app.component('OxdTableFilter', OxdTableFilter);
    app.component('SubmitButton', SubmitButton);
    app.component('TableHeader', TableHeader);
    app.component('RequiredText', RequiredText);
    app.component('DateInput', DateInput);
    app.component('TimeInput', TimeInput);
  },
};
