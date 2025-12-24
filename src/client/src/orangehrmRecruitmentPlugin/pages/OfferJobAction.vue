<!--
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */
 -->

<template>
  <div class="orangehrm-background-container">
    <candidate-action-layout
      v-model:loading="isLoading"
      :candidate-id="candidateId"
      :title="$t('recruitment.offer_job')"
      @submit-valid="onSave"
    >
      <oxd-form-row>
        <oxd-grid :cols="3">
          <oxd-grid-item class="--span-column-2">
            <oxd-input-field
              v-model="note"
              :rules="rules.note"
              :label="$t('general.notes')"
              :placeholder="$t('general.type_here')"
              required
              type="textarea"
            />
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>

      <oxd-form-row>
        <oxd-grid :cols="3" class="orangehrm-full-width-grid">
          <oxd-grid-item>
            <oxd-input-field
              v-model="attachment.attachment"
              type="file"
              :label="$t('general.select_file')"
              :button-label="$t('general.browse')"
              :rules="rules.attachment"
              :placeholder="$t('general.no_file_selected')"
              required
            />
            <oxd-text class="orangehrm-input-hint" tag="p">
              {{ $t('general.accepts_up_to_n_mb', {count: formattedFileSize}) }}
            </oxd-text>
          </oxd-grid-item>
        </oxd-grid>
      </oxd-form-row>

      <oxd-divider />
      <oxd-form-actions>
        <oxd-button
          display-type="ghost"
          :label="$t('general.cancel')"
          @click="onClickBack"
        />
        <submit-button />
      </oxd-form-actions>
    </candidate-action-layout>
  </div>
</template>

<script>
import {
  required,
  maxFileSize,
  validFileTypes,
  shouldNotExceedCharLength,
} from '@ohrm/core/util/validation/rules';
import CandidateActionLayout from '@/orangehrmRecruitmentPlugin/components/CandidateActionLayout';
import {APIService} from '@/core/util/services/api.service';
import {navigate} from '@/core/util/helper/navigation';

export default {
  components: {
    'candidate-action-layout': CandidateActionLayout,
  },
  props: {
    candidateId: {
      type: Number,
      required: true,
    },
    maxFileSize: {
      type: Number,
      required: true,
    },
    allowedFileTypes: {
      type: Array,
      required: true,
    },
  },

  setup(props) {
    const http = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/recruitment/candidates/${props.candidateId}/job/offer`,
    );

    const historyHttp = new APIService(
      window.appGlobal.baseUrl,
      `/api/v2/recruitment/candidates/${props.candidateId}/history`,
    );

    return {
      http,
      historyHttp,
    };
  },
  data() {
    return {
      isLoading: false,
      note: null,
      interviewId: null,
      attachment: {
        attachment: null,
        comment: '',
      },
      attachmentUrlTemplate:
        '/api/v2/recruitment/interviews/{interviewId}/attachments',
      rules: {
        note: [shouldNotExceedCharLength(2000), required],
        attachment: [
          required,
          maxFileSize(this.maxFileSize),
          validFileTypes(this.allowedFileTypes),
        ],
      },
    };
  },

  computed: {
    formattedFileSize() {
      return Math.round((this.maxFileSize / (1024 * 1024)) * 100) / 100;
    },
  },

  mounted() {
    this.fetchInterviewId();
  },

  methods: {
    fetchInterviewId() {
      this.historyHttp
        .request({
          method: 'GET',
          params: {
            limit: 1,
          },
        })
        .then((response) => {
          const historyItems = response.data.data;
          if (historyItems && historyItems.length > 0) {
            const latestInterviewHistory = historyItems.find(
              (item) => item.interview && item.interview.id,
            );
            if (latestInterviewHistory) {
              this.interviewId = latestInterviewHistory.interview.id;
            } else {
              console.log(
                'No recent interview history found for this candidate.',
              );
            }
          }
        })
        .catch((error) => {
          console.error('Error fetching candidate history:', error);
        });
    },

    onSave() {
      this.isLoading = true;
      const attachmentToUpload = this.attachment.attachment;
      const interviewIdForUpload = this.interviewId;

      const saveOfferJob = () => {
        return this.http.request({
          method: 'PUT',
          data: {
            note: this.note,
          },
        });
      };

      const uploadAttachment = () => {
        if (!attachmentToUpload || !interviewIdForUpload) {
          return Promise.resolve();
        }
        const resolvedUrl = this.attachmentUrlTemplate.replace(
          '{interviewId}',
          this.interviewId,
        );
        const uploadService = new APIService(
          window.appGlobal.baseUrl,
          resolvedUrl,
        );

        return uploadService
          .create({
            attachment: attachmentToUpload,
            comment: this.attachment.comment,
          })
          .then(() => {
            return this.$toast.updateSuccess();
          })
          .catch((error) => {
            console.error('File upload failed:', error);
            this.$toast.showError(
              'Job Offer saved, but attachment upload failed.',
            );
          });
      };

      uploadAttachment()
        .then(saveOfferJob)
        .then(() => {
          navigate('/recruitment/addCandidate/{id}', {id: this.candidateId});
        })
        .catch((error) => {
          console.error('Job Offer status update failed:', error);
          this.$toast.showError('Failed to update candidate status.');
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    onClickBack() {
      navigate('/recruitment/addCandidate/{id}', {id: this.candidateId});
    },
  },
};
</script>
